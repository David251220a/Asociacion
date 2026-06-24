const swalWithBootstrapButtons = swal.mixin({
    confirmButtonClass: 'btn btn-success btn-rounded',
    cancelButtonClass: 'btn btn-danger btn-rounded mr-3',
    buttonsStyling: false,
})

document.addEventListener('livewire:load', function () {

    Livewire.on('mensaje_error', function (msj) {
        swalWithBootstrapButtons(
            'Atención',
            msj,
            'error'
        );
    });

    Livewire.on('mensaje_exitoso', function (msj) {
        swal({
            title: 'Buen Trabajo',
            text: msj,
            type: 'success',
            padding: '2em'
        });
    });

});
