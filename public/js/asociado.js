$(document).ready(function () {
    $('.basic').select2({
        width: '100%'
    });

    $('#departamento_id').on('change', function () {
        let departamentoId = $(this).val();
        let $distrito = $('#distrito_id');
        let $ciudad = $('#ciudad_id');

        $distrito.empty();
        $ciudad.empty();

        if (!departamentoId) {
            $distrito.trigger('change');
            $ciudad.trigger('change');
            return;
        }

        fetch(`/distritos/${departamentoId}`)
            .then(response => response.json())
            .then(data => {
                $distrito.empty();
                $ciudad.empty();

                data.forEach(function (item) {
                    $distrito.append(
                        `<option value="${item.id}">${item.descripcion}</option>`
                    );
                });

                if (data.length > 0) {
                    $distrito.val(data[0].id).trigger('change');
                } else {
                    $distrito.trigger('change');
                    $ciudad.trigger('change');
                }
            })
            .catch(error => {
                console.error('Error al cargar distritos:', error);
                $distrito.empty().trigger('change');
                $ciudad.empty().trigger('change');
            });
    });

    $('#distrito_id').on('change', function () {
        let distritoId = $(this).val();
        let $ciudad = $('#ciudad_id');

        $ciudad.empty();

        if (!distritoId) {
            $ciudad.trigger('change');
            return;
        }

        fetch(`/ciudades/${distritoId}`)
            .then(response => response.json())
            .then(data => {
                $ciudad.empty();

                data.forEach(function (item) {
                    $ciudad.append(
                        `<option value="${item.id}">${item.descripcion}</option>`
                    );
                });

                if (data.length > 0) {
                    $ciudad.val(data[0].id).trigger('change');
                } else {
                    $ciudad.trigger('change');
                }
            })
            .catch(error => {
                console.error('Error al cargar ciudades:', error);
                $ciudad.empty().trigger('change');
            });
    });

    $('#departamento_id').trigger('change');
});

let enviado = false;
document.querySelector('form').addEventListener('submit', function (e) {

    if (enviado) {
        e.preventDefault();
        return false;
    }

    enviado = true;

    let btn = document.getElementById('btnEnviar');
    btn.disabled = true;
    btn.innerText = 'Enviando...';

});

// document.addEventListener('DOMContentLoaded', function () {
//     const tipo = document.getElementById('tipo_vivienda_id');
//     const otro = document.getElementById('otro');

//     tipo.addEventListener('change', function () {

//         if (this.value == 3) {
//             // activar
//             otro.removeAttribute('readonly');
//             otro.focus();
//         } else {
//             // limpiar y desactivar
//             otro.value = '';
//             otro.setAttribute('readonly', true);
//         }

//     });
// });
