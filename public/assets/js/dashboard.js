document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');

    sidebarToggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('open');
    });

    document.addEventListener('click', (event) => {
        if (
            window.innerWidth <= 820 &&
            sidebar?.classList.contains('open') &&
            !sidebar.contains(event.target) &&
            !sidebarToggle?.contains(event.target)
        ) {
            sidebar.classList.remove('open');
        }
    });

    const clock = document.getElementById('liveClock');
    const updateClock = () => {
        if (!clock) return;
        const now = new Date();
        const value = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        }).format(now);
        clock.textContent = `${value} WIB`;
    };
    updateClock();
    setInterval(updateClock, 1000);

    const canvas = document.getElementById('performanceChart');
    if (!canvas) return;

    let values = [];
    try {
        values = JSON.parse(canvas.dataset.values || '[]');
    } catch (_) {
        values = [];
    }

    const draw = () => {
        const ratio = window.devicePixelRatio || 1;
        const width = canvas.clientWidth;
        const height = canvas.clientHeight;
        canvas.width = Math.max(1, Math.floor(width * ratio));
        canvas.height = Math.max(1, Math.floor(height * ratio));

        const context = canvas.getContext('2d');
        context.scale(ratio, ratio);
        context.clearRect(0, 0, width, height);

        const padding = { top: 16, right: 12, bottom: 12, left: 12 };
        const chartWidth = width - padding.left - padding.right;
        const chartHeight = height - padding.top - padding.bottom;

        context.lineWidth = 1;
        context.strokeStyle = 'rgba(148, 174, 207, 0.10)';
        for (let row = 0; row <= 4; row += 1) {
            const y = padding.top + (chartHeight / 4) * row;
            context.beginPath();
            context.moveTo(padding.left, y);
            context.lineTo(width - padding.right, y);
            context.stroke();
        }

        if (values.length < 2) return;

        const min = Math.min(...values) - 8;
        const max = Math.max(...values) + 5;
        const points = values.map((value, index) => ({
            x: padding.left + (chartWidth / (values.length - 1)) * index,
            y: padding.top + chartHeight - ((value - min) / (max - min)) * chartHeight,
        }));

        const fill = context.createLinearGradient(0, padding.top, 0, height);
        fill.addColorStop(0, 'rgba(89, 123, 232, 0.26)');
        fill.addColorStop(1, 'rgba(89, 123, 232, 0)');

        context.beginPath();
        context.moveTo(points[0].x, height - padding.bottom);
        points.forEach((point, index) => {
            if (index === 0) {
                context.lineTo(point.x, point.y);
                return;
            }

            const previous = points[index - 1];
            const midX = (previous.x + point.x) / 2;
            context.bezierCurveTo(midX, previous.y, midX, point.y, point.x, point.y);
        });
        context.lineTo(points[points.length - 1].x, height - padding.bottom);
        context.closePath();
        context.fillStyle = fill;
        context.fill();

        const gradient = context.createLinearGradient(padding.left, 0, width - padding.right, 0);
        gradient.addColorStop(0, '#5d7ce8');
        gradient.addColorStop(1, '#00cce7');

        context.beginPath();
        points.forEach((point, index) => {
            if (index === 0) {
                context.moveTo(point.x, point.y);
                return;
            }

            const previous = points[index - 1];
            const midX = (previous.x + point.x) / 2;
            context.bezierCurveTo(midX, previous.y, midX, point.y, point.x, point.y);
        });
        context.lineWidth = 2.5;
        context.strokeStyle = gradient;
        context.lineCap = 'round';
        context.lineJoin = 'round';
        context.stroke();

        points.forEach((point, index) => {
            if (index !== points.length - 1) return;
            context.beginPath();
            context.arc(point.x, point.y, 5, 0, Math.PI * 2);
            context.fillStyle = '#0e1d30';
            context.fill();
            context.lineWidth = 3;
            context.strokeStyle = '#00cce7';
            context.stroke();
        });
    };

    draw();
    window.addEventListener('resize', draw);
});
