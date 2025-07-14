function statsOverviewStatChart({ dataChecksum, labels, values }) {
    return {
        init: function () {
            const canvas = this.$refs.canvas;
            const ctx = canvas.getContext('2d');
            
            // Get theme colors
            const backgroundColorElement = this.$refs.backgroundColorElement;
            const borderColorElement = this.$refs.borderColorElement;
            
            const backgroundColor = window.getComputedStyle(backgroundColorElement).color;
            const borderColor = window.getComputedStyle(borderColorElement).color;
            
            // Create gradient
            const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
            gradient.addColorStop(0, backgroundColor.replace('rgb', 'rgba').replace(')', ', 0.2)'));
            gradient.addColorStop(1, backgroundColor.replace('rgb', 'rgba').replace(')', ', 0.02)'));
            
            // Chart configuration
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: gradient,
                        borderColor: borderColor,
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 0,
                        pointHitRadius: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            enabled: false,
                        },
                    },
                    elements: {
                        point: {
                            radius: 0,
                        },
                    },
                    scales: {
                        x: {
                            display: false,
                        },
                        y: {
                            display: false,
                        },
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeInOutQuart',
                    },
                    animations: {
                        tension: {
                            duration: 1000,
                            easing: 'linear',
                            from: 1,
                            to: 0,
                            loop: true
                        }
                    }
                }
            });
        }
    };
}
