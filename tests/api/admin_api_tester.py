#!/usr/bin/env python3
"""
Admin API Testing Script - Python Version
Testing comprehensive admin endpoints with various conditions
"""

import requests
import json
import time
import sys
from datetime import datetime, date
from typing import Dict, Any, List, Optional

class AdminApiTester:
    def __init__(self, base_url: str = "http://localhost:8000/api/v1"):
        self.base_url = base_url
        self.admin_token = None
        self.test_results = []
        self.created_resources = {}
        self.session = requests.Session()
        
    def setup_auth(self, pn: str = "ADMIN01", password: str = "password123") -> bool:
        """Login and get admin token"""
        print("🔧 Setting up authentication...")
        
        login_data = {
            "pn": pn,
            "password": password,
            "device_name": "Python Test Client"
        }
        
        try:
            response = self.session.post(
                f"{self.base_url}/auth/login",
                json=login_data,
                headers={"Accept": "application/json"}
            )
            
            if response.status_code == 200:
                data = response.json()
                self.admin_token = data.get("data", {}).get("token")
                self.session.headers.update({
                    "Authorization": f"Bearer {self.admin_token}",
                    "Accept": "application/json"
                })
                print(f"✅ Authentication successful. Token: {self.admin_token[:20]}...")
                return True
            else:
                print(f"❌ Authentication failed: {response.status_code} - {response.text}")
                return False
                
        except Exception as e:
            print(f"❌ Authentication error: {str(e)}")
            return False
    
    def make_request(self, method: str, endpoint: str, data: Optional[Dict] = None) -> Dict[str, Any]:
        """Make HTTP request with proper error handling"""
        url = f"{self.base_url}{endpoint}"
        headers = {"Content-Type": "application/json"} if data else {}
        
        try:
            if method.upper() == "GET":
                response = self.session.get(url)
            elif method.upper() == "POST":
                response = self.session.post(url, json=data, headers=headers)
            elif method.upper() == "PUT":
                response = self.session.put(url, json=data, headers=headers)
            elif method.upper() == "DELETE":
                response = self.session.delete(url)
            else:
                raise ValueError(f"Unsupported method: {method}")
            
            return {
                "status_code": response.status_code,
                "data": response.json() if response.content else {},
                "success": response.status_code < 400,
                "headers": dict(response.headers)
            }
            
        except requests.exceptions.RequestException as e:
            return {
                "status_code": 0,
                "data": {"error": str(e)},
                "success": False,
                "headers": {}
            }
        except json.JSONDecodeError:
            return {
                "status_code": response.status_code,
                "data": {"error": "Invalid JSON response", "content": response.text},
                "success": False,
                "headers": dict(response.headers)
            }
    
    def log_result(self, test_name: str, success: bool, details: str = "") -> None:
        """Log test result"""
        self.test_results.append({
            "test": test_name,
            "success": success,
            "details": details,
            "timestamp": datetime.now().isoformat()
        })
        
        status = "✅" if success else "❌"
        print(f"{status} {test_name}", end="")
        if details:
            print(f" - {details}")
        else:
            print()
    
    def test_dashboard_endpoints(self) -> None:
        """Test dashboard KPIs and charts"""
        print("\n📊 Testing Dashboard Endpoints...")
        
        # Test KPIs
        response = self.make_request("GET", "/admin/dashboard/kpis")
        success = response["success"] and "data" in response["data"]
        self.log_result("Dashboard KPIs", success, f"Status: {response['status_code']}")
        
        # Test KPIs with branch filter
        response = self.make_request("GET", "/admin/dashboard/kpis?branchId=1")
        success = response["success"]
        self.log_result("Dashboard KPIs - Branch Filter", success)
        
        # Test Charts
        response = self.make_request("GET", "/admin/dashboard/charts")
        success = (response["success"] and 
                  "deviceConditions" in response["data"].get("data", {}) and
                  "devicesPerBranch" in response["data"].get("data", {}))
        self.log_result("Dashboard Charts", success)
    
    def test_device_management(self) -> None:
        """Test device CRUD operations"""
        print("\n🖥️ Testing Device Management...")
        
        # Test list devices
        response = self.make_request("GET", "/admin/devices")
        success = response["success"] and "data" in response["data"]
        self.log_result("List Devices", success)
        
        # Test search and filtering
        response = self.make_request("GET", "/admin/devices?search=Dell&page=1&perPage=5")
        self.log_result("Search Devices", response["success"])
        
        response = self.make_request("GET", "/admin/devices?condition=Baik")
        self.log_result("Filter by Condition", response["success"])
        
        # Test device details
        response = self.make_request("GET", "/admin/devices/1")
        success = response["success"] and "deviceId" in response["data"].get("data", {})
        self.log_result("Device Details", success)
        
        # Test create device
        self.test_create_device()
        
        # Test update device
        if "device" in self.created_resources:
            self.test_update_device()
        
        # Test delete device
        if "device" in self.created_resources:
            self.test_delete_device()
    
    def test_create_device(self) -> None:
        """Test device creation with validation"""
        timestamp = int(time.time())
        
        device_data = {
            "brand": "Python Test Brand",
            "brand_name": f"Test Model {timestamp}",
            "serial_number": f"PY-TEST-{timestamp}",
            "asset_code": f"PY/TEST/{timestamp}",
            "bribox_id": "01",  # Assuming this exists
            "condition": "Baik",
            "spec1": "Test CPU",
            "spec2": "8GB RAM",
            "spec3": "256GB SSD",
            "dev_date": "2024-07-21"
        }
        
        response = self.make_request("POST", "/admin/devices", device_data)
        success = response["success"] and response["status_code"] == 201
        
        if success and "deviceId" in response["data"].get("data", {}):
            self.created_resources["device"] = response["data"]["data"]["deviceId"]
        
        self.log_result("Create Device", success)
        
        # Test validation - duplicate serial number
        response = self.make_request("POST", "/admin/devices", device_data)
        validation_works = not response["success"] and response["status_code"] == 422
        self.log_result("Create Device - Validation", validation_works)
    
    def test_update_device(self) -> None:
        """Test device update"""
        device_id = self.created_resources["device"]
        
        update_data = {
            "condition": "Perlu Pengecekan",
            "spec4": "Updated by Python Test"
        }
        
        response = self.make_request("PUT", f"/admin/devices/{device_id}", update_data)
        success = response["success"] and response["status_code"] == 200
        self.log_result("Update Device", success)
    
    def test_delete_device(self) -> None:
        """Test device deletion"""
        device_id = self.created_resources["device"]
        
        response = self.make_request("DELETE", f"/admin/devices/{device_id}")
        success = response["success"] and response["status_code"] == 200
        self.log_result("Delete Device", success)
    
    def test_device_assignments(self) -> None:
        """Test device assignment management"""
        print("\n📋 Testing Device Assignment Management...")
        
        # Test list assignments
        response = self.make_request("GET", "/admin/device-assignments")
        success = response["success"] and "data" in response["data"]
        self.log_result("List Device Assignments", success)
        
        # Test filtering
        response = self.make_request("GET", "/admin/device-assignments?activeOnly=true&page=1&perPage=5")
        self.log_result("Filter Active Assignments", response["success"])
        
        response = self.make_request("GET", "/admin/device-assignments?status=Digunakan")
        self.log_result("Filter by Status", response["success"])
        
        # Test create assignment
        self.test_create_assignment()
        
        # Test update assignment
        if "assignment" in self.created_resources:
            self.test_update_assignment()
        
        # Test return device
        if "assignment" in self.created_resources:
            self.test_return_device()
    
    def test_create_assignment(self) -> None:
        """Test assignment creation"""
        # Get first available device and user
        devices_response = self.make_request("GET", "/admin/devices?page=1&perPage=1")
        users_response = self.make_request("GET", "/admin/users?page=1&perPage=1")
        
        if (not devices_response["success"] or not users_response["success"] or
            not devices_response["data"].get("data") or not users_response["data"].get("data")):
            self.log_result("Create Assignment", False, "No devices or users available")
            return
        
        device_id = devices_response["data"]["data"][0]["deviceId"]
        user_id = users_response["data"]["data"][0]["userId"]
        
        assignment_data = {
            "device_id": device_id,
            "user_id": user_id,
            "assigned_date": "2024-07-21",
            "status": "Digunakan",
            "notes": "Python test assignment"
        }
        
        response = self.make_request("POST", "/admin/device-assignments", assignment_data)
        success = response["success"] and response["status_code"] == 201
        
        if success and "assignmentId" in response["data"].get("data", {}):
            self.created_resources["assignment"] = response["data"]["data"]["assignmentId"]
        
        self.log_result("Create Assignment", success)
    
    def test_update_assignment(self) -> None:
        """Test assignment update"""
        assignment_id = self.created_resources["assignment"]
        
        update_data = {
            "status": "Tidak Digunakan",
            "notes": "Updated by Python test"
        }
        
        response = self.make_request("PUT", f"/admin/device-assignments/{assignment_id}", update_data)
        success = response["success"] and response["status_code"] == 200
        self.log_result("Update Assignment", success)
    
    def test_return_device(self) -> None:
        """Test device return"""
        assignment_id = self.created_resources["assignment"]
        
        return_data = {
            "returned_date": "2024-07-21",
            "return_notes": "Python test return"
        }
        
        response = self.make_request("POST", f"/admin/device-assignments/{assignment_id}/return", return_data)
        success = response["success"] and response["status_code"] == 200
        self.log_result("Return Device", success)
        
        # Test validation - return already returned device
        response = self.make_request("POST", f"/admin/device-assignments/{assignment_id}/return", return_data)
        validation_works = (not response["success"] and 
                           response["data"].get("errorCode") == "ERR_DEVICE_ALREADY_RETURNED")
        self.log_result("Return Device - Validation", validation_works)
    
    def test_user_management(self) -> None:
        """Test user management endpoints"""
        print("\n👥 Testing User Management...")
        
        # Test list users
        response = self.make_request("GET", "/admin/users")
        success = response["success"] and "data" in response["data"]
        self.log_result("List Users", success)
        
        # Test search users
        response = self.make_request("GET", "/admin/users?search=admin&page=1&perPage=5")
        self.log_result("Search Users", response["success"])
        
        # Test filter by department
        response = self.make_request("GET", "/admin/users?departmentId=1")
        self.log_result("Filter Users by Department", response["success"])
    
    def test_master_data(self) -> None:
        """Test master data endpoints"""
        print("\n📚 Testing Master Data...")
        
        # Test branches
        response = self.make_request("GET", "/admin/branches")
        success = response["success"] and "data" in response["data"]
        self.log_result("List Branches", success)
        
        # Test categories
        response = self.make_request("GET", "/admin/categories")
        success = response["success"] and "data" in response["data"]
        self.log_result("List Categories", success)
    
    def test_authentication_and_authorization(self) -> None:
        """Test authentication and authorization"""
        print("\n🔐 Testing Authentication & Authorization...")
        
        # Test without token
        temp_session = requests.Session()
        temp_session.headers.update({"Accept": "application/json"})
        response = temp_session.get(f"{self.base_url}/admin/devices")
        auth_required = response.status_code == 401
        self.log_result("Authentication Required", auth_required)
        
        # Test with invalid token
        temp_session.headers.update({"Authorization": "Bearer invalid_token"})
        response = temp_session.get(f"{self.base_url}/admin/devices")
        invalid_token = response.status_code == 401
        self.log_result("Invalid Token Rejected", invalid_token)
    
    def test_error_handling(self) -> None:
        """Test error handling"""
        print("\n🚫 Testing Error Handling...")
        
        # Test 404 - non-existent device
        response = self.make_request("GET", "/admin/devices/99999")
        not_found = response["status_code"] == 404
        self.log_result("404 - Device Not Found", not_found)
        
        # Test 422 - invalid data
        invalid_data = {
            "brand": "",  # Required field
            "serial_number": "test"
        }
        response = self.make_request("POST", "/admin/devices", invalid_data)
        validation_error = (response["status_code"] == 422 and 
                           "errors" in response["data"])
        self.log_result("422 - Validation Error", validation_error)
    
    def test_pagination_and_filtering(self) -> None:
        """Test pagination and advanced filtering"""
        print("\n📄 Testing Pagination & Filtering...")
        
        # Test pagination
        response = self.make_request("GET", "/admin/devices?page=1&perPage=2")
        success = (response["success"] and 
                  "meta" in response["data"] and
                  "currentPage" in response["data"]["meta"])
        self.log_result("Pagination", success)
        
        # Test search across multiple fields
        response = self.make_request("GET", "/admin/devices?search=test")
        self.log_result("Multi-field Search", response["success"])
        
        # Test assignment filtering
        response = self.make_request("GET", "/admin/device-assignments?branchId=1&activeOnly=true")
        self.log_result("Complex Assignment Filtering", response["success"])
    
    def run_all_tests(self) -> None:
        """Run all test suites"""
        print("🚀 Starting Python Admin API Tests...")
        print(f"Base URL: {self.base_url}")
        print("=" * 50)
        
        if not self.setup_auth():
            print("❌ Authentication failed. Cannot continue with tests.")
            return
        
        self.test_authentication_and_authorization()
        self.test_dashboard_endpoints()
        self.test_device_management()
        self.test_device_assignments()
        self.test_user_management()
        self.test_master_data()
        self.test_pagination_and_filtering()
        self.test_error_handling()
        
        self.print_summary()
        self.save_results()
    
    def print_summary(self) -> None:
        """Print test summary"""
        print("\n" + "=" * 50)
        print("📊 TEST SUMMARY")
        print("=" * 50)
        
        total = len(self.test_results)
        passed = sum(1 for result in self.test_results if result["success"])
        failed = total - passed
        
        print(f"Total Tests: {total}")
        print(f"✅ Passed: {passed}")
        print(f"❌ Failed: {failed}")
        print(f"Success Rate: {passed/total*100:.2f}%")
        
        if failed > 0:
            print("\nFailed Tests:")
            for result in self.test_results:
                if not result["success"]:
                    print(f"  ❌ {result['test']} - {result['details']}")
        
        print("\n🎉 Testing completed!")
    
    def save_results(self) -> None:
        """Save test results to JSON file"""
        filename = f"admin_api_test_results_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        results_data = {
            "summary": {
                "total_tests": len(self.test_results),
                "passed": sum(1 for r in self.test_results if r["success"]),
                "failed": sum(1 for r in self.test_results if not r["success"]),
                "timestamp": datetime.now().isoformat(),
                "base_url": self.base_url
            },
            "results": self.test_results,
            "created_resources": self.created_resources
        }
        
        try:
            with open(filename, 'w') as f:
                json.dump(results_data, f, indent=2)
            print(f"📄 Test results saved to: {filename}")
        except Exception as e:
            print(f"⚠️ Could not save results: {str(e)}")


def main():
    """Main function"""
    if len(sys.argv) > 1:
        base_url = sys.argv[1]
    else:
        base_url = "http://localhost:8000/api/v1"
    
    tester = AdminApiTester(base_url)
    tester.run_all_tests()


if __name__ == "__main__":
    main()
