<?php
// Este archivo debe estar ubicado en /ourcenter/estudiantes/includes/estudiante-footer.php
?>

            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle con Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js para los gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Script para inicializar los gráficos -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar gráfico de progreso si existe el canvas
        const canvasProgreso = document.getElementById('graficoProgresoCursos');
        if (canvasProgreso) {
            const datos = JSON.parse(canvasProgreso.getAttribute('data-cursos'));
            const cursos = datos.map(item => item.nombre);
            const progresos = datos.map(item => item.progreso);
            
            new Chart(canvasProgreso, {
                type: 'bar',
                data: {
                    labels: cursos,
                    datasets: [{
                        label: 'Progreso %',
                        data: progresos,
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(255, 206, 86, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(153, 102, 255, 0.6)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    });
    </script>

    <!-- Script personalizado -->
    <script>
    // Activar todos los tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    </script>
</body>
</html>