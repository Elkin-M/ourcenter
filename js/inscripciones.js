// ==================== ARCHIVO: assets/js/inscripciones.js ====================
$(document).ready(function() {
    // Variables globales
    let tablaInscripciones;
    let salonesData = [];
    let estudiantesData = [];
    
    // Inicializar la página
    init();
    
    function init() {
        initializeDataTable();
        loadStats();
        loadInscripciones();
        loadSalones();
        loadEstudiantes();
        bindEvents();
    }
    
    // Inicializar DataTable
    function initializeDataTable() {
        tablaInscripciones = $('#tabla-inscripciones').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            responsive: true,
            processing: true,
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: [6] }
            ]
        });
    }
    
    // Cargar estadísticas
    function loadStats() {
        $.get('../api/inscripciones.php?action=stats')
            .done(function(response) {
                if (response.success) {
                    updateStatsDisplay(response.data);
                }
            })
            .fail(function() {
                showMessage('Error al cargar las estadísticas', 'error');
            });
    }
    
    // Actualizar display de estadísticas
    function updateStatsDisplay(stats) {
        $('.dashboard-stats').eq(0).find('.stat-number').text(stats.total || 0);
        $('.dashboard-stats').eq(1).find('.stat-number').text(stats.activas || 0);
        $('.dashboard-stats').eq(2).find('.stat-number').text(stats.pendientes || 0);
        $('.dashboard-stats').eq(3).find('.stat-number').text(stats.pagos_pendientes || 0);
    }
    
    // Cargar inscripciones
    function loadInscripciones() {
        $.get('../api/inscripciones.php?action=list')
            .done(function(response) {
                if (response.success) {
                    populateInscripcionesTable(response.data);
                }
            })
            .fail(function() {
                showMessage('Error al cargar las inscripciones', 'error');
            });
    }
    
    // Poblar tabla de inscripciones
    function populateInscripcionesTable(inscripciones) {
        tablaInscripciones.clear();
        
        inscripciones.forEach(function(inscripcion) {
            const estadoBadge = getEstadoBadge(inscripcion.estado);
            const pagoBadge = getPagoBadge(inscripcion.pago_estado);
            const fechaInscripcion = new Date(inscripcion.fecha_inscripcion).toLocaleDateString('es-ES');
            
            const row = [
                inscripcion.id,
                `<div class="d-flex align-items-center">
                    <div class="inscripcion-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="fw-bold">${inscripcion.estudiante_nombre} ${inscripcion.estudiante_apellido}</div>
                        <small class="text-muted">${inscripcion.estudiante_email}</small>
                    </div>
                </div>`,
                `<div class="inscripcion-curso">
                    <img src="https://via.placeholder.com/40x40/0a1b5c/ffffff?text=${inscripcion.curso_nombre.charAt(0)}" alt="Curso">
                    <div>
                        <div>${inscripcion.curso_nombre}</div>
                        <small class="text-muted">${inscripcion.salon_nombre}</small>
                    </div>
                </div>`,
                fechaInscripcion,
                estadoBadge,
                pagoBadge,
                `<div class="btn-group">
                    <button type="button" class="btn btn-sm btn-primary ver-detalles" data-id="${inscripcion.id}">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-warning editar-inscripcion" data-id="${inscripcion.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger eliminar-inscripcion" data-id="${inscripcion.id}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>`
            ];
            
            tablaInscripciones.row.add(row);
        });
        
        tablaInscripciones.draw();
    }
    
    // Obtener badge de estado
    function getEstadoBadge(estado) {
        const badges = {
            'Activa': 'estado-activa',
            'Pendiente': 'estado-pendiente',
            'Cancelada': 'estado-cancelada',
            'Completa': 'estado-completa'
        };
        
        const className = badges[estado] || 'estado-pendiente';
        return `<span class="badge ${className}">${estado}</span>`;
    }
    
    // Obtener badge de pago
    function getPagoBadge(estadoPago) {
        if (!estadoPago || estadoPago === null) {
            return '<span class="badge pago-nulo">Sin pago</span>';
        }
        
        const badges = {
            'Completado': 'pago-completado',
            'Pendiente': 'pago-pendiente'
        };
        
        const className = badges[estadoPago] || 'pago-pendiente';
        const texto = estadoPago === 'Completado' ? 'Pagado' : estadoPago;
        return `<span class="badge ${className}">${texto}</span>`;
    }
    
    // Cargar salones disponibles
    function loadSalones() {
        $.get('../api/salones.php?action=disponibles')
            .done(function(response) {
                if (response.success) {
                    salonesData = response.data;
                    populateSalonesSelect();
                }
            })
            .fail(function() {
                showMessage('Error al cargar los salones', 'error');
            });
    }
    
    // Poblar select de salones
    function populateSalonesSelect() {
        const select = $('#curso-select');
        select.empty().append('<option value="">Selecciona un salón...</option>');
        
        salonesData.forEach(function(salon) {
            const option = `<option value="${salon.id}" 
                                   data-salon="${salon.nombre}" 
                                   data-precio="${salon.precio}" 
                                   data-duracion="${salon.horario}"
                                   data-cupos="${salon.cupos_disponibles}">
                                ${salon.curso_nombre} - ${salon.nombre} (${salon.cupos_disponibles} cupos)
                            </option>`;
            select.append(option);
        });
    }
    
    // Cargar estudiantes
    function loadEstudiantes() {
        $.get('../api/estudiantes.php')
            .done(function(response) {
                if (response.success) {
                    estudiantesData = response.data;
                    populateEstudiantesList();
                }
            })
            .fail(function() {
                showMessage('Error al cargar los estudiantes', 'error');
            });
    }
    
    // Poblar lista de estudiantes
    function populateEstudiantesList() {
        const container = $('#lista-estudiantes');
        container.empty();
        
        estudiantesData.forEach(function(estudiante) {
            const estudianteCard = `
                <div class="estudiante-card" data-id="${estudiante.id}">
                    <div class="form-check">
                        <input class="form-check-input estudiante-checkbox" type="checkbox" 
                               value="${estudiante.id}" id="estudiante-${estudiante.id}">
                        <label class="form-check-label w-100" for="estudiante-${estudiante.id}">
                            <div class="d-flex align-items-center">
                                <div class="inscripcion-avatar me-2">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">${estudiante.nombre_completo}</div>
                                    <small class="text-muted">${estudiante.email}</small>
                                    ${estudiante.telefono ? `<br><small class="text-muted">Tel: ${estudiante.telefono}</small>` : ''}
                                </div>
                            </div>
                        </label>
                    </div>
                </div>`;
            container.append(estudianteCard);
        });
    }
    
    // Bind events
    function bindEvents() {
        // Cambio en selección de curso/salón
        $('#curso-select').on('change', function() {
            const selectedOption = $(this).find(':selected');
            if (selectedOption.val()) {
                showCursoInfo(selectedOption);
                updateResumenInscripcion();
            } else {
                hideCursoInfo();
                updateResumenInscripcion();
            }
        });
        
        // Búsqueda de estudiantes
        $('#buscar-estudiantes').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            filterEstudiantes(searchTerm);
        });
        
        // Seleccionar todos los estudiantes
        $('#seleccionar-todos').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.estudiante-checkbox:visible').prop('checked', isChecked).trigger('change');
        });
        
        // Cambio en selección de estudiantes
        $(document).on('change', '.estudiante-checkbox', function() {
            updateContadorSeleccionados();
            updateResumenInscripcion();
            validateForm();
            
            // Actualizar estado de "seleccionar todos"
            const totalVisible = $('.estudiante-checkbox:visible').length;
            const totalChecked = $('.estudiante-checkbox:visible:checked').length;
            $('#seleccionar-todos').prop('checked', totalVisible > 0 && totalVisible === totalChecked);
        });
        
        // Submit del formulario de nueva inscripción
        $('#formNuevaInscripcion').on('submit', function(e) {
            e.preventDefault();
            createInscripciones();
        });
        
        // Submit del formulario de nuevo estudiante
        $('#formNuevoEstudiante').on('submit', function(e) {
            e.preventDefault();
            createEstudiante();
        });
        
        // Ver detalles de inscripción
        $(document).on('click', '.ver-detalles', function() {
            const inscripcionId = $(this).data('id');
            showInscripcionDetails(inscripcionId);
        });
        
        // Eliminar inscripción
        $(document).on('click', '.eliminar-inscripcion', function() {
            const inscripcionId = $(this).data('id');
            confirmDeleteInscripcion(inscripcionId);
        });
        
        // Cambiar estado desde el modal de detalles
        $(document).on('click', '.cambiar-estado', function() {
            const nuevoEstado = $(this).data('estado');
            const inscripcionId = $('#modalDetallesInscripcion').data('inscripcion-id');
            updateInscripcionStatus(inscripcionId, nuevoEstado);
        });
        
        // Filtros
        $('#filtro-global').on('input', function() {
            tablaInscripciones.search($(this).val()).draw();
        });
        
        $('#filtro-estado, #filtro-pago').on('change', function() {
            applyFilters();
        });
        
        $('#reset-filtros').on('click', function() {
            resetFilters();
        });
    }
    
    // Mostrar información del curso seleccionado
    function showCursoInfo(selectedOption) {
        $('#curso-salon').text(selectedOption.data('salon'));
        $('#curso-precio').text(selectedOption.data('precio'));
        $('#curso-duracion').text(selectedOption.data('duracion'));
        $('#curso-info').show();
    }
    
    // Ocultar información del curso
    function hideCursoInfo() {
        $('#curso-info').hide();
    }
    
    // Filtrar estudiantes
    function filterEstudiantes(searchTerm) {
        $('.estudiante-card').each(function() {
            const text = $(this).text().toLowerCase();
            const shouldShow = text.includes(searchTerm);
            $(this).toggle(shouldShow);
        });
        
        updateContadorSeleccionados();
    }
    
    // Actualizar contador de estudiantes seleccionados
    function updateContadorSeleccionados() {
        const count = $('.estudiante-checkbox:checked').length;
        $('#contador-seleccionados').text(count);
    }
    
    // Actualizar resumen de inscripción
    function updateResumenInscripcion() {
        const salonId = $('#curso-select').val();
        const estudiantesSeleccionados = $('.estudiante-checkbox:checked').length;
        
        if (!salonId || estudiantesSeleccionados === 0) {
            $('#resumen-inscripcion').html(`
                <div class="text-center text-muted">
                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                    <p>Selecciona un salón y estudiantes para ver el resumen</p>
                </div>
            `);
            return;
        }
        
        const selectedOption = $('#curso-select').find(':selected');
        const precio = parseFloat(selectedOption.data('precio')) || 0;
        const total = precio * estudiantesSeleccionados;
        
        $('#resumen-inscripcion').html(`
            <div class="row">
                <div class="col-12 mb-2">
                    <strong>Salón seleccionado:</strong><br>
                    <small class="text-muted">${selectedOption.text()}</small>
                </div>
                <div class="col-6">
                    <strong>Estudiantes:</strong><br>
                    <span class="badge bg-primary">${estudiantesSeleccionados}</span>
                </div>
                <div class="col-6">
                    <strong>Precio unitario:</strong><br>
                    <span class="text-success">${precio.toFixed(2)}</span>
                </div>
                <div class="col-12 mt-2">
                    <hr>
                    <strong>Total estimado:</strong><br>
                    <span class="text-success h5">${total.toFixed(2)}</span>
                </div>
            </div>
        `);
    }
    
    // Validar formulario
    function validateForm() {
        const salonSeleccionado = $('#curso-select').val();
        const estudiantesSeleccionados = $('.estudiante-checkbox:checked').length;
        
        const isValid = salonSeleccionado && estudiantesSeleccionados > 0;
        $('#btn-crear-inscripcion').prop('disabled', !isValid);
    }
    
    // Crear inscripciones
    function createInscripciones() {
        const formData = {
            salon_id: $('#curso-select').val(),
            estudiantes_ids: $('.estudiante-checkbox:checked').map(function() {
                return parseInt($(this).val());
            }).get(),
            estado: $('#estado-inscripcion').val(),
            metodo_inscripcion: $('#metodo-inscripcion').val(),
            notas: $('#notas-inscripcion').val(),
            enviar_notificacion: $('#enviar-notificacion').is(':checked')
        };
        
        $('#btn-crear-inscripcion').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Creando...');
        
        $.ajax({
            url: 'api/inscripciones.php',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                showMessage(`Se crearon ${response.inscripciones_creadas} inscripciones exitosamente`, 'success');
                $('#modalNuevaInscripcion').modal('hide');
                loadInscripciones();
                loadStats();
                resetInscripcionForm();
            } else {
                showMessage(response.error || 'Error al crear las inscripciones', 'error');
            }
        })
        .fail(function() {
            showMessage('Error de conexión al crear las inscripciones', 'error');
        })
        .always(function() {
            $('#btn-crear-inscripcion').prop('disabled', false).html('<i class="fas fa-save me-2"></i>Crear Inscripciones');
        });
    }
    
    // Crear estudiante
    function createEstudiante() {
        const formData = {
            nombre: $('#nuevo-nombre').val(),
            apellido: $('#nuevo-apellido').val(),
            email: $('#nuevo-email').val(),
            telefono: $('#nuevo-telefono').val(),
            documento: $('#nuevo-documento').val()
        };
        
        $.ajax({
            url: 'api/estudiantes.php',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                showMessage('Estudiante creado exitosamente', 'success');
                $('#modalNuevoEstudiante').modal('hide');
                
                // Añadir el nuevo estudiante a la lista
                estudiantesData.push(response.data);
                populateEstudiantesList();
                
                // Limpiar formulario
                $('#formNuevoEstudiante')[0].reset();
            } else {
                showMessage(response.error || 'Error al crear el estudiante', 'error');
            }
        })
        .fail(function() {
            showMessage('Error de conexión al crear el estudiante', 'error');
        });
    }
    
    // Mostrar detalles de inscripción
    function showInscripcionDetails(inscripcionId) {
        $.get(`../api/inscripciones.php?action=details&id=${inscripcionId}`)
            .done(function(response) {
                if (response.success) {
                    populateInscripcionModal(response.data);
                    $('#modalDetallesInscripcion').data('inscripcion-id', inscripcionId).modal('show');
                } else {
                    showMessage('Error al cargar los detalles', 'error');
                }
            })
            .fail(function() {
                showMessage('Error de conexión al cargar los detalles', 'error');
            });
    }
    
    // Poblar modal de detalles
    function populateInscripcionModal(inscripcion) {
        $('#modal-estudiante-nombre').text(`${inscripcion.estudiante_nombre} ${inscripcion.estudiante_apellido}`);
        $('#modal-estudiante-email').text(inscripcion.estudiante_email);
        $('#modal-estudiante-telefono').text(inscripcion.estudiante_telefono || 'No registrado');
        
        $('#modal-curso-nombre').text(`${inscripcion.curso_nombre} - ${inscripcion.salon_nombre}`);
        $('#modal-fecha-inscripcion').text(new Date(inscripcion.fecha_inscripcion).toLocaleDateString('es-ES'));
        $('#modal-ultima-actualizacion').text(new Date(inscripcion.updated_at || inscripcion.created_at).toLocaleDateString('es-ES'));
        
        const estadoBadge = getEstadoBadge(inscripcion.estado);
        const pagoBadge = getPagoBadge(inscripcion.pago_estado);
        
        $('#modal-estado-badge').removeClass().addClass('badge').addClass(getEstadoBadgeClass(inscripcion.estado)).text(inscripcion.estado);
        $('#modal-pago-badge').removeClass().addClass('badge').addClass(getPagoBadgeClass(inscripcion.pago_estado)).text(inscripcion.pago_estado || 'Sin pago');
    }
    
    // Obtener clase CSS para badge de estado
    function getEstadoBadgeClass(estado) {
        const classes = {
            'Activa': 'estado-activa',
            'Pendiente': 'estado-pendiente',
            'Cancelada': 'estado-cancelada',
            'Completa': 'estado-completa'
        };
        return classes[estado] || 'estado-pendiente';
    }
    
    // Obtener clase CSS para badge de pago
    function getPagoBadgeClass(estadoPago) {
        if (!estadoPago) return 'pago-nulo';
        
        const classes = {
            'Completado': 'pago-completado',
            'Pendiente': 'pago-pendiente'
        };
        return classes[estadoPago] || 'pago-pendiente';
    }
    
    // Actualizar estado de inscripción
    function updateInscripcionStatus(inscripcionId, nuevoEstado) {
        const data = {
            inscripcion_id: inscripcionId,
            estado: nuevoEstado
        };
        
        $.ajax({
            url: '../api/inscripciones.php',
            method: 'POST',
            data: { action: 'update_status', ...data },
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                showMessage('Estado actualizado correctamente', 'success');
                $('#modalDetallesInscripcion').modal('hide');
                loadInscripciones();
                loadStats();
            } else {
                showMessage(response.error || 'Error al actualizar el estado', 'error');
            }
        })
        .fail(function() {
            showMessage('Error de conexión al actualizar el estado', 'error');
        });
    }
    
    // Confirmar eliminación de inscripción
    function confirmDeleteInscripcion(inscripcionId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteInscripcion(inscripcionId);
            }
        });
    }
    
    // Eliminar inscripción
    function deleteInscripcion(inscripcionId) {
        $.ajax({
            url: `../api/inscripciones.php?id=${inscripcionId}`,
            method: 'DELETE',
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                showMessage('Inscripción eliminada correctamente', 'success');
                loadInscripciones();
                loadStats();
            } else {
                showMessage(response.error || 'Error al eliminar la inscripción', 'error');
            }
        })
        .fail(function() {
            showMessage('Error de conexión al eliminar la inscripción', 'error');
        });
    }
    
    // Aplicar filtros
    function applyFilters() {
        const estadoFilter = $('#filtro-estado').val();
        const pagoFilter = $('#filtro-pago').val();
        
        $.fn.dataTable.ext.search.pop(); // Remover filtros anteriores
        
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const estado = data[4]; // Columna de estado (HTML)
            const pago = data[5]; // Columna de pago (HTML)
            
            let estadoMatch = true;
            let pagoMatch = true;
            
            if (estadoFilter) {
                estadoMatch = estado.includes(estadoFilter);
            }
            
            if (pagoFilter) {
                if (pagoFilter === 'null') {
                    pagoMatch = pago.includes('Sin pago');
                } else if (pagoFilter === 'Completado') {
                    pagoMatch = pago.includes('Pagado');
                } else {
                    pagoMatch = pago.includes(pagoFilter);
                }
            }
            
            return estadoMatch && pagoMatch;
        });
        
        tablaInscripciones.draw();
    }
    
    // Resetear filtros
    function resetFilters() {
        $('#filtro-global').val('');
        $('#filtro-estado').val('');
        $('#filtro-pago').val('');
        
        $.fn.dataTable.ext.search.pop();
        tablaInscripciones.search('').draw();
    }
    
    // Resetear formulario de inscripción
    function resetInscripcionForm() {
        $('#formNuevaInscripcion')[0].reset();
        $('.estudiante-checkbox').prop('checked', false);
        $('#seleccionar-todos').prop('checked', false);
        updateContadorSeleccionados();
        hideCursoInfo();
        updateResumenInscripcion();
        validateForm();
    }
    
    // Mostrar mensajes
    function showMessage(message, type) {
        const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('#mensajes-container').html(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
});