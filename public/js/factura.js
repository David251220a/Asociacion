const swalWithBootstrapButtons = swal.mixin({
    confirmButtonClass: 'btn btn-success btn-rounded',
    cancelButtonClass: 'btn btn-danger btn-rounded mr-3',
    buttonsStyling: false,
})

window.addEventListener('load', function() {

    window.livewire.on('mensaje_error', msj => {
        swalWithBootstrapButtons(
            'Atención',
            msj,
            'error'
        )
    });

    window.livewire.on('mensaje_exitoso', msj => {
        swal({
            title: 'Buen Trabajo',
            text: msj,
            type: 'success',
            padding: '2em'
        })
    });

});

function anular(id)
{
    swal({
        title: 'Esta seguro de anular esta factura?',
        text: "No puede revertir la operacion!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        padding: '2em'
    }).then(function(result) {
        if (result.value) {
            Livewire.emit('anular_factura', id);
        }
    })
}