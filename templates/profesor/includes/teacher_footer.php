
</main>
        </div>
    </div>

    <!-- Scripts del profesor -->
    <script src="assets/js/teacher.js"></script>
    
    <!-- Scripts adicionales por página -->
    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Script inline personalizado -->
    <?php if (isset($inline_scripts)): ?>
        <script>
            <?php echo $inline_scripts; ?>
        </script>
    <?php endif; ?>

    <!-- Toast notifications -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toast-template" class="toast align-items-center border-0 d-none" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <!-- Modal genérico -->
    <div class="modal fade" id="generic-modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary modal-confirm">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Script global para el panel de profesor
        $(document).ready(function() {
            // Configurar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Configurar popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });

            // Función para mostrar toast
            window.showToast = function(message, type = 'info') {
                const toastTemplate = document.getElementById('toast-template');
                const toast = toastTemplate.cloneNode(true);
                toast.id = 'toast-' + Date.now();
                toast.classList.remove('d-none');
                toast.classList.add('text-bg-' + type);
                toast.querySelector('.toast-body').textContent = message;
                
                document.querySelector('.toast-container').appendChild(toast);
                
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
                
                toast.addEventListener('hidden.bs.toast', function() {
                    toast.remove();
                });
            };

            // Función para mostrar modal genérico
            window.showModal = function(title, body, onConfirm) {
                const modal = document.getElementById('generic-modal');
                modal.querySelector('.modal-title').textContent = title;
                modal.querySelector('.modal-body').innerHTML = body;
                
                const confirmBtn = modal.querySelector('.modal-confirm');
                confirmBtn.onclick = function() {
                    if (onConfirm) onConfirm();
                    bootstrap.Modal.getInstance(modal).hide();
                };
                
                new bootstrap.Modal(modal).show();
            };

            // Auto-refresh para notificaciones (cada 5 minutos)
            setInterval(function() {
                checkNotifications();
            }, 300000);

            function checkNotifications() {
                $.get('api/teacher_notifications.php', function(data) {
                    if (data.notifications && data.notifications.length > 0) {
                        // Actualizar contador de notificaciones si existe
                        const badge = document.querySelector('.notification-badge');
                        if (badge) {
                            badge.textContent = data.notifications.length;
                            badge.classList.remove('d-none');
                        }
                    }
                }).fail(function() {
                    console.log('Error al obtener notificaciones');
                });
            }

            // Confirmar acciones de eliminación
            $('.btn-delete').on('click', function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                const item = $(this).data('item') || 'este elemento';
                
                showModal(
                    'Confirmar Eliminación',
                    `¿Está seguro de que desea eliminar ${item}? Esta acción no se puede deshacer.`,
                    function() {
                        window.location.href = href;
                    }
                );
            });

            // Manejar formularios AJAX
            $('.ajax-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalText = submitBtn.html();
                
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
                
                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method') || 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showToast(response.message, 'success');
                            if (response.redirect) {
                                setTimeout(function() {
                                    window.location.href = response.redirect;
                                }, 1500);
                            }
                        } else {
                            showToast(response.message || 'Error en el servidor', 'danger');
                        }
                    },
                    error: function() {
                        showToast('Error de conexión', 'danger');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
</body>
</html>
