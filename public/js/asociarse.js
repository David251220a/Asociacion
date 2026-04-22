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
