const swalWithBootstrapButtons = swal.mixin({
    confirmButtonClass: 'btn btn-success btn-rounded',
    cancelButtonClass: 'btn btn-danger btn-rounded mr-3',
    buttonsStyling: false,
});

function aplicarMascaraFecha() {
    let IM = window.Inputmask;

    if (IM && IM.default) {
        IM = IM.default;
    }

    const input = document.getElementById('fecha_nacimiento');

    if (!IM || !input) {
        return;
    }

    IM({
        mask: "99/99/9999",
        placeholder: "dd/mm/aaaa",
        clearIncomplete: false
    }).mask(input);
}

document.addEventListener('livewire:load', function () {

    setTimeout(aplicarMascaraFecha, 300);

    Livewire.hook('message.processed', function () {
        setTimeout(aplicarMascaraFecha, 100);
    });

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
