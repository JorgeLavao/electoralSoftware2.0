import Swal from 'sweetalert2'

function normalize(params) {
    // Livewire v3 payload
    return Array.isArray(params[0])
        ? params[0][0]
        : params[0]
}

/**
 * ALERTA NORMAL
 */
window.handleAlert = function (params) {
    const data = normalize(params)

    Swal.fire({
        icon: data.icon ?? 'info',
        title: data.title ?? '',
        text: data.text ?? '',
        html: data.html ?? null,
        timer: data.timer ?? null,
        timerProgressBar: !!data.timer,
        confirmButtonText: data.confirmButtonText ?? 'Aceptar',
    })
}

/**
 * ALERTA DE CONFIRMACIÓN
 */
window.handleConfirmAlert = function (params) {
    const data = normalize(params)

    Swal.fire({
        icon: data.icon ?? 'question',
        title: data.title ?? '',
        text: data.text ?? '',
        showCancelButton: true,
        confirmButtonText: data.confirmButtonText ?? 'Sí',
        cancelButtonText: data.cancelButtonText ?? 'Cancelar',
    }).then(result => {
        if (result.isConfirmed) {
            Livewire.dispatch(data.action, data.params ?? [])
        }
    })
}
