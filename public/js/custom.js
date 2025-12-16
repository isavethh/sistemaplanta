// Scripts personalizados para PlantaCRUDS

// Inicialización de DataTables con configuración por defecto
function initDataTable(tableId, options = {}) {
    const defaultOptions = {
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copiar',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-sm btn-info'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm btn-success'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-sm btn-danger'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimir',
                className: 'btn btn-sm btn-primary'
            }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        ...options
    };
    
    return $(tableId).DataTable(defaultOptions);
}

// Confirmación de eliminación con SweetAlert (si está disponible)
function confirmDelete(message = '¿Estás seguro de eliminar este registro?') {
    if (typeof Swal !== 'undefined') {
        return Swal.fire({
            title: '¿Estás seguro?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });
    } else {
        return confirm(message);
    }
}

// Auto-hide alerts después de 5 segundos
$(document).ready(function() {
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

// Agregar animación de carga a los formularios
$('form').on('submit', function() {
    const submitBtn = $(this).find('button[type="submit"]');
    submitBtn.prop('disabled', true);
    submitBtn.html('<span class="loading"></span> Guardando...');
});

// Validación de campos numéricos
$('input[type="number"]').on('keypress', function(e) {
    if (e.which < 48 || e.which > 57) {
        if (e.which !== 8 && e.which !== 46) { // Allow backspace and decimal point
            e.preventDefault();
        }
    }
});

// Tooltips de Bootstrap
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

// Popovers de Bootstrap
$(function () {
    $('[data-toggle="popover"]').popover();
});

// Scroll suave
$('a[href*="#"]:not([href="#"])').click(function() {
    if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') 
        && location.hostname == this.hostname) {
        var target = $(this.hash);
        target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 70
            }, 1000);
            return false;
        }
    }
});

// Mensajes de éxito con animación
function showSuccessMessage(message) {
    const alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
        '<i class="fas fa-check-circle"></i> ' + message +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '</div>');
    
    $('.content').prepend(alert);
    
    setTimeout(function() {
        alert.fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

// Mensajes de error con animación
function showErrorMessage(message) {
    const alert = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
        '<i class="fas fa-exclamation-circle"></i> ' + message +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '</div>');
    
    $('.content').prepend(alert);
    
    setTimeout(function() {
        alert.fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

// Formato de números
function formatNumber(number, decimals = 2) {
    return new Intl.NumberFormat('es-MX', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(number);
}

// Formato de moneda
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(amount);
}

// Función para cargar datos dinámicamente
function loadData(url, containerId) {
    $.ajax({
        url: url,
        method: 'GET',
        beforeSend: function() {
            $(containerId).html('<div class="text-center"><span class="loading"></span> Cargando...</div>');
        },
        success: function(data) {
            $(containerId).html(data);
        },
        error: function() {
            $(containerId).html('<div class="alert alert-danger">Error al cargar los datos</div>');
        }
    });
}

// Función para mostrar alertas usando modal en lugar de alert() nativo
function showAlert(message, title = 'Información', iconClass = 'fa-info-circle', headerClass = 'bg-info') {
    // Asegurar que el modal existe, si no, crear uno dinámicamente
    if ($('#modalAlert').length === 0) {
        $('body').append(`
            <div class="modal fade" id="modalAlert" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title" id="modalAlertTitle">
                                <i class="fas fa-info-circle"></i> Información
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p id="modalAlertMessage"></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">Aceptar</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }
    
    $('#modalAlertTitle').html('<i class="fas ' + iconClass + '"></i> ' + title);
    $('#modalAlertMessage').html(message.replace(/\n/g, '<br>'));
    $('#modalAlert .modal-header').removeClass('bg-info bg-warning bg-danger bg-success').addClass(headerClass);
    $('#modalAlert').modal('show');
}

// Reemplazar alert() nativo globalmente
if (typeof window.alert === 'function') {
    window._nativeAlert = window.alert;
    window.alert = function(message) {
        showAlert(message);
    };
}

