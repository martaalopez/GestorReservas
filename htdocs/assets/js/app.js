// Funciones auxiliares 

function escapar(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}

function fecha(texto) {
    if (!texto) return '—';
    const [anio, mes, dia] = String(texto).split(' ')[0].split('-');
    if (!anio || !mes || !dia) return '—';
    return dia + '/' + mes + '/' + anio;
}

function dinero(numero) {
    if (numero === null || numero === undefined || numero === '') return '—';
    return Number(numero).toFixed(2).replace('.', ',');
}

// Muestra un mensaje en la página (verde = ok, rojo = error)
function mensaje(texto, esError) {
    const caja = document.getElementById('mensaje-estado');
    if (!caja) return;
    caja.textContent = texto;
    caja.style.color = esError ? 'red' : 'green';
}


//  Mostrar y  ocultar el formulario de crear 
const botonMostrar = document.getElementById('btn-mostrar-crear');
const seccionCrear = document.getElementById('seccion-crear');

if (botonMostrar && seccionCrear) {
    botonMostrar.addEventListener('click', function () {
        seccionCrear.style.display = seccionCrear.style.display === 'block' ? 'none' : 'block';
    });
}


//  Calcular el precio
const selectHotel = document.getElementById('select-hotel');
const inputEntrada = document.querySelector('#form-crear [name="fecha_entrada"]');
const inputSalida = document.querySelector('#form-crear [name="fecha_salida"]');
const inputImporte = document.getElementById('input-importe');

function calcularImporte() {
    if (!selectHotel || !inputEntrada || !inputSalida || !inputImporte) return;

    const precioNoche = Number(selectHotel.selectedOptions[0].dataset.precio);
    const milisegundos = new Date(inputSalida.value) - new Date(inputEntrada.value);
    const noches = Math.round(milisegundos / (1000 * 60 * 60 * 24));

    if (precioNoche > 0 && noches > 0) {
        inputImporte.value = (precioNoche * noches).toFixed(2);
    } else {
        inputImporte.value = '';
    }
}

if (selectHotel) {
    selectHotel.addEventListener('change', calcularImporte);
    inputEntrada.addEventListener('change', calcularImporte);
    inputSalida.addEventListener('change', calcularImporte);
}


//  Filtrar 
const formFiltros = document.getElementById('form-filtros');

if (formFiltros) {
    formFiltros.addEventListener('submit', async function (e) {
        e.preventDefault();
        mensaje('Cargando...', false);

        try {

            const params = new URLSearchParams();
            for (const [k, v] of new FormData(formFiltros)) {
                if (v !== '') params.append(k, v);
            }

            const respuesta = await fetch('/reservas?' + params.toString());
            const datos = await respuesta.json();

            if (!respuesta.ok) {
                mensaje('Error: ' + (datos.error || 'no se pudo filtrar'), true);
                return;
            }

            const tabla = document.querySelector('#tabla-reservas tbody');
            tabla.innerHTML = '';

            if (datos.length === 0) {
                tabla.innerHTML = '<tr><td colspan="7">No hay reservas con esos filtros.</td></tr>';
            }

            for (const r of datos) {
                let acciones = '<a href="/vistas/reservas/' + r.id + '" class="btn btn-sm btn-info">Detalle</a> ';
                if (r.estado === 'pendiente' || r.estado === 'confirmada') {
                    acciones += '<button class="btn btn-sm btn-danger btn-cancelar" data-id="' + r.id + '">Cancelar</button>';
                }

                tabla.innerHTML +=
                    '<tr data-id="' + r.id + '">' +
                    '<td>' + escapar(r.nombre_huesped) + '</td>' +
                    '<td>' + escapar(r.nombre_alojamiento) + '</td>' +
                    '<td>' + fecha(r.fecha_entrada) + '</td>' +
                    '<td>' + fecha(r.fecha_salida) + '</td>' +
                    '<td><span class="badge badge-' + escapar(r.estado) + '">' + escapar(r.estado) + '</span></td>' +
                    '<td>' + dinero(r.importe) + ' €</td>' +
                    '<td>' + acciones + '</td>' +
                    '</tr>';
            }

            mensaje('', false);
        } catch (error) {
            mensaje('Error de red al cargar los datos', true);
        }
    });
}


//  Cancelar una reserva 
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

            const fila = document.querySelector('#tabla-reservas tbody tr[data-id="' + id + '"]');
            if (fila) {

                const badge = fila.querySelector('.badge');
                badge.textContent = 'cancelada';
                badge.className = 'badge badge-cancelada';


                fila.lastElementChild.innerHTML = '<a href="/vistas/reservas/' + id + '" class="btn btn-sm btn-info">Detalle</a> ';
            }


            const contenedorAcciones = document.querySelector('.detalle-acciones');
            if (contenedorAcciones) {

                contenedorAcciones.style.display = 'none';

                const badge = document.querySelector('.detalle-cabecera .badge');
                if (badge) {
                    badge.textContent = 'cancelada';
                    badge.className = 'badge badge-cancelada';
                }
            }

            mensaje('Reserva cancelada correctamente', false);

        } else {
            const datos = await respuesta.json().catch(() => ({}));
            alert('No se pudo cancelar: ' + (datos.error || 'error desconocido'));
        }
    } catch (error) {
        alert('Error de red al cancelar');
    }
});


// Crear una reserva 
const formCrear = document.getElementById('form-crear');
const erroresCrear = document.getElementById('errores-crear');

if (formCrear) {
    formCrear.addEventListener('submit', async function (e) {
        e.preventDefault();
        erroresCrear.innerHTML = '';
        mensaje('Guardando...', false);

        try {
            const respuesta = await fetch('/reservas', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(Object.fromEntries(new FormData(formCrear)))
            });

            if (respuesta.ok) {
                mensaje('Reserva creada correctamente', false);
                setTimeout(function () { location.reload(); }, 1000);
                return;
            }

            // Errores que devuelve el servidor 
            const datos = await respuesta.json();
            mensaje('', false);

            if (datos.detalles) {
                for (const campo in datos.detalles) {
                    erroresCrear.innerHTML += '<div>' + escapar(campo) + ': ' + escapar(datos.detalles[campo]) + '</div>';
                }
            } else {
                erroresCrear.textContent = 'Error: ' + (datos.error || 'revisa los datos');
            }
        } catch (error) {
            mensaje('', false);
            erroresCrear.textContent = 'Error de red al conectar con la API';
        }
    });
}