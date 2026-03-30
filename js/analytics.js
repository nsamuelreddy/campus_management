function loadAnalytics() {
    fetch('api/analytics.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const chartContainer = document.querySelector('.bar-chart');
                if (!chartContainer) return;
                
                chartContainer.innerHTML = ''; // Clear hardcoded bars

                // Dynamically build the bars based on PHP data
                data.chartData.forEach(month => {
                    // Calculate heights (max 120px for visual scale)
                    const totalHeight = Math.min(120, month.total * 5);
                    const resolvedHeight = Math.min(120, month.resolved * 5);

                    const html = `
                        <div class="bar-group">
                            <div class="bars">
                                <div class="bar" style="height: ${totalHeight}px;" title="Total: ${month.total}"></div>
                                <div class="bar resolved" style="height: ${resolvedHeight}px;" title="Resolved: ${month.resolved}"></div>
                            </div>
                            <div class="bar-label">${month.month}</div>
                        </div>
                    `;
                    chartContainer.insertAdjacentHTML('beforeend', html);
                });
            }
        })
        .catch(err => console.error("Error loading analytics:", err));
}

document.addEventListener('DOMContentLoaded', loadAnalytics);