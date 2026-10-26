document.addEventListener('DOMContentLoaded', () => {

    // ========== 1. MOSTRAR / OCULTAR EL FORMULARIO DE CREAR ==========
    const botonMostrar = document.getElementById('btn-mostrar-crear');
    const seccionCrear = document.getElementById('seccion-crear');

    if (botonMostrar) {
        botonMostrar.addEventListener('click', function () {
            // Alternamos entre mostrar y ocultar usando el estilo CSS
            if (seccionCrear.style.display === 'none' || seccionCrear.style.display === '') {
                seccionCrear.style.display = 'block';
            } else {
                seccionCrear.style.display = 'none';
            }
        });
    }

    // ========== 2. FILTRAR ==========
    const formFiltros = document.getElementById('form-filtros');
    const mensajeEstado = document.getElementById('mensaje-estado'); // Para feedback

    if (formFiltros) {
        formFiltros.addEventListener('submit', async function (e) {
            e.preventDefault();

            mensajeEstado.textContent = 'Cargando...';
            mensajeEstado.style.color = 'blue';

            const params = new URLSearchParams(new FormData(formFiltros));

            try {
                const respuesta = await fetch('/reservas?' + params);
                const reservas = await respuesta.json();

                const tabla = document.querySelector('#tabla-reservas tbody');
                tabla.innerHTML = '';

                for (const r of reservas) {
                    let boton = '';
                    if (r.estado === 'pendiente') {
                        boton = '<button class="btn btn-sm btn-danger btn-cancelar" data-id="' + r.id + '">Cancelar</button>';
                    }

                    // Añadimos clases CSS bonitas a la tabla repintada
                    tabla.innerHTML += '<tr>' +
                        '<td>' + r.nombre_huesped + '</td>' +
                        '<td>' + r.nombre_alojamiento + '</td>' +
                        '<td>' + r.fecha_entrada + '</td>' +
                        '<td>' + r.fecha_salida + '</td>' +
                        '<td><span class="badge badge-' + r.estado + '">' + r.estado + '</span></td>' +
                        '<td>' + r.importe + ' €</td>' +
                        '<td>' + boton + '</td>' +
                        '</tr>';
                }
                mensajeEstado.textContent = ''; // Limpiamos el mensaje
            } catch (error) {
                mensajeEstado.textContent = 'Error al cargar los datos';
                mensajeEstado.style.color = 'red';
            }
        });
    }

    // ========== 3. CANCELAR UNA RESERVA ==========
    document.addEventListener('click', async function (e) {
        if (!e.target.classList.contains('btn-cancelar')) {
            return;
        }

        if (!confirm('¿Seguro que quieres cancelar esta reserva?')) {
            return;
        }

        const id = e.target.dataset.id;

        try {
            const respuesta = await fetch('/reservas/' + id + '/estado', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ estado: 'cancelada' })
            });

            if (respuesta.ok) {
                location.reload(); // Recargamos para ver el cambio
            } else {
                alert('Error al cancelar en el servidor');
            }
        } catch (error) {
            alert('Error de red al cancelar');
        }
    });

    // ========== 4. CREAR UNA RESERVA ==========
    const formCrear = document.getElementById('form-crear');
    const erroresCrear = document.getElementById('errores-crear');

    if (formCrear) {
        formCrear.addEventListener('submit', async function (e) {
            e.preventDefault();

            erroresCrear.textContent = ''; // Limpiar errores anteriores
            mensajeEstado.textContent = 'Guardando...';
            mensajeEstado.style.color = 'blue';

            const datos = Object.fromEntries(new FormData(formCrear));

            try {
                const respuesta = await fetch('/reservas', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });

                if (respuesta.ok) {
                    location.reload(); // Recargamos si todo va bien
                } else {
                    // Si falla (ej. validación 422), mostramos el error
                    const errorData = await respuesta.json();
                    mensajeEstado.textContent = '';
                    erroresCrear.textContent = 'Error: ' + (errorData.error || 'Revisa los datos del formulario');
                }
            } catch (error) {
                mensajeEstado.textContent = '';
                erroresCrear.textContent = 'Error de red al conectar con la API';
            }
        });
    }
});