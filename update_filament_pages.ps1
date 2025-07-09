# PowerShell script to update remaining Filament Create and Edit pages
# Add redirect to index after save/create

$createFiles = @(
    "app\Filament\Resources\BriboxResource\Pages\CreateBribox.php",
    "app\Filament\Resources\BriboxesCategoryResource\Pages\CreateBriboxesCategory.php", 
    "app\Filament\Resources\DepartmentResource\Pages\CreateDepartment.php",
    "app\Filament\Resources\MainBranchResource\Pages\CreateMainBranch.php",
    "app\Filament\Resources\AssignmentLetterResource\Pages\CreateAssignmentLetter.php"
)

$editFiles = @(
    "app\Filament\Resources\BriboxResource\Pages\EditBribox.php",
    "app\Filament\Resources\BriboxesCategoryResource\Pages\EditBriboxesCategory.php",
    "app\Filament\Resources\DepartmentResource\Pages\EditDepartment.php", 
    "app\Filament\Resources\MainBranchResource\Pages\EditMainBranch.php",
    "app\Filament\Resources\DeviceAssignmentResource\Pages\EditDeviceAssignment.php",
    "app\Filament\Resources\AssignmentLetterResource\Pages\EditAssignmentLetter.php"
)

foreach ($file in $createFiles) {
    if (Test-Path $file) {
        $content = Get-Content $file -Raw
        if ($content -notmatch "getRedirectUrl") {
            $pattern = '(protected static \$resource = [^;]+;)'
            $replacement = '$1

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl(''index'');
    }'
            $newContent = $content -replace $pattern, $replacement
            Set-Content $file $newContent
            Write-Host "Updated: $file"
        }
    }
}

foreach ($file in $editFiles) {
    if (Test-Path $file) {
        $content = Get-Content $file -Raw
        if ($content -notmatch "getRedirectUrl") {
            $pattern = '(protected static \$resource = [^;]+;)'
            $replacement = '$1

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl(''index'');
    }'
            $newContent = $content -replace $pattern, $replacement
            Set-Content $file $newContent
            Write-Host "Updated: $file"
        }
    }
}

Write-Host "Batch update completed!"
