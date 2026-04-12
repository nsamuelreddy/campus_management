function loadAnalytics() {
    
    fetch('api/analytics.php', {
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {

        //  HANDLE ERROR (IMPORTANT)
        if (!data.success) {
            console.log("ERROR:", data.message);
            alert("Unauthorized / Session expired");
            return;
        }

        
        // BAR CHART (NO DESIGN CHANGE)
        
        const chartContainer = document.querySelector('.bar-chart');
        if (!chartContainer) return;
        
        chartContainer.innerHTML = ''; // Clear hardcoded bars

        data.chartData.forEach(month => {

            const maxValue = 40; // same as Y axis
            const totalHeight = (month.total / maxValue) * 120;
            const resolvedHeight = (month.resolved / maxValue) * 120;

            const html = `
                <div class="bar-group"
                    onmouseenter="showTooltip(event, '${month.month}', ${month.total}, ${month.resolved})"
                    onmouseleave="hideTooltip()">
         
                    <div class="bars">
                         <div class="bar" style="height: ${totalHeight}px;"></div>
                        <div class="bar resolved" style="height: ${resolvedHeight}px;"></div>
                    </div>

                    <div class="bar-label">${month.month}</div>
                </div>
            `;
            chartContainer.insertAdjacentHTML('beforeend', html);
        });

        
        // LINE CHART (ADDED ONLY)
        
        const svg = document.querySelector('.line svg');

        if (svg) {
            let points = '';
            const step = 200 / (data.feedbackData.length - 1 || 1);

            data.feedbackData.forEach((item, index) => {
                const x = index * step;
                const y = 100 - (item.rating * 20); // scale (out of 5)

                points += `${x},${y} `;
            });

            svg.innerHTML = `
                <polyline
                    points="${points}"
                    fill="none"
                    stroke="#3b82f6"
                    stroke-width="3"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            `;
        }

    })
    .catch(err => console.error("Error loading analytics:", err));
}


function showTooltip(event, month, total, resolved) {
    const tooltip = document.getElementById('chartTooltip');

    tooltip.style.display = 'block';
    tooltip.style.left = event.pageX + 10 + 'px';
    tooltip.style.top = event.pageY - 40 + 'px';

    tooltip.innerHTML = `
        <strong>${month}</strong><br>
        <span style="color:#3b82f6">complaints : ${total}</span><br>
        <span style="color:#10b981">resolved : ${resolved}</span>
    `;
}

function hideTooltip() {
    document.getElementById('chartTooltip').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', loadAnalytics);
