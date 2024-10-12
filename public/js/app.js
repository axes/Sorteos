// Debug
let version = '1.0.4';
console.log('JS cargado correctamente. version:', version);

// Función de enmascarado de RUT, mostrando solo los primeros 4 caracteres
function enmascararRut(rut) {
    return rut.slice(0, 4) + '*****';
}

// Función para mostrar alertas de SweetAlert
function mostrarAlerta(icon, title, text) {
    Swal.fire({ icon, title, text, confirmButtonText: 'Aceptar' });
}

// Función para enmascarar el email, mostrando solo los primeros dos caracteres
function enmascararEmail(email) {
    const partes = email.split('@');
    return partes[0].slice(0, 2) + '***@' + partes[1];
}


// Crear Sorteo
const crearSorteoForm = document.getElementById('crearSorteoForm');
if (crearSorteoForm) {
    crearSorteoForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('/sorteos/crear', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/sorteos/editar/' + data.sorteo_id;
                } else {
                    mostrarAlerta('error', 'Error', data.message);
                }
            })
            .catch(() => mostrarAlerta('error', 'Error', 'Error al crear el sorteo.'));
    });
}

// Confirmar logout
const logoutLink = document.querySelector('a.nav-link[href="/logout"]');
if (logoutLink) {
    logoutLink.addEventListener('click', function (event) {
        event.preventDefault();
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Vas a cerrar la sesión!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cerrar sesión',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) window.location.href = '/logout';
        });
    });
}

// Seleccionar tipo de sorteo (crear/editar)
function mostrarSiguientePaso() {
    const opcionSeleccionada = document.querySelector('input[name="sorteoOption"]:checked').value;
    document.getElementById('crearSorteo').style.display = opcionSeleccionada === 'crear' ? 'block' : 'none';
    document.getElementById('editarSorteo').style.display = opcionSeleccionada === 'editar' ? 'block' : 'none';
}

// Función para cargar y actualizar participantes
function actualizarParticipantes(participantes) {
    const listaParticipantesDiv = document.getElementById('listaParticipantes');
    const totalParticipantesSpan = document.getElementById('totalParticipantes');

    totalParticipantesSpan.textContent = `(${participantes.length})`;
    listaParticipantesDiv.innerHTML = participantes.length > 0
        ? `
            <ul class="list-group list-unstyled">
                ${participantes.map(p => `
                    <li class="list-group-item">
                        ${p.nombre} - ${enmascararRut(p.rut)}
                        ${!p.baneado
                ? `<button class="btn btn-warning btn-sm banear-participante" data-participante-id="${p.id}">Banear</button>`
                : `<span class="text-danger">(Baneado)</span>
                               <button class="btn btn-secondary btn-sm desbanear-participante" data-participante-id="${p.id}">Desbanear</button>`
            }
                    </li>
                `).join('')}
            </ul>
        `
        : '<p>No hay participantes cargados.</p>';
}

// Cargar participantes desde archivo CSV
const cargarParticipantesForm = document.getElementById('cargarParticipantesForm');
if (cargarParticipantesForm) {
    cargarParticipantesForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('/sorteos/cargar-participantes', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: data.message });
                    cargarParticipantesForm.reset();
                    actualizarParticipantes(data.participantes);
                    if (data.participantes.length > 0) {
                        document.getElementById('realizarSorteo').style.display = 'block';
                    }
                } else {
                    mostrarAlerta('error', 'Error', data.message);
                }
            })
            .catch(() => mostrarAlerta('error', 'Error', 'Error al cargar los participantes.'));
    });
}

// Función para realizar el sorteo con cuenta regresiva
// OLD BUT GOLD
// const realizarSorteoForm = document.getElementById('realizarSorteoForm');
// if (realizarSorteoForm) {
//     realizarSorteoForm.addEventListener('submit', function (e) {
//         e.preventDefault();
//         const formData = new FormData(this);

//         Swal.fire({
//             title: 'Preparando el sorteo...',
//             html: 'El sorteo comenzará en <b>3</b> segundos.',
//             timer: 3000,
//             timerProgressBar: true,
//             didOpen: () => {
//                 Swal.showLoading();
//                 const b = Swal.getHtmlContainer().querySelector('b');
//                 let timeLeft = 3;
//                 const interval = setInterval(() => b.textContent = timeLeft--, 1000);
//                 setTimeout(() => clearInterval(interval), 3000);
//             }
//         }).then(() => {
//             fetch('/sorteos/realizar-sorteo', { method: 'POST', body: formData })
//                 .then(response => response.json())
//                 .then(data => {
//                     if (data.success) {
//                         Swal.fire({ icon: 'success', title: '¡Felicitaciones!', html: data.html }).then(() => location.reload());
//                     } else {
//                         mostrarAlerta('error', 'Error', data.message);
//                     }
//                 })
//                 .catch(() => mostrarAlerta('error', 'Error', 'Error al realizar el sorteo.'));
//         });
//     });
// }

// Realizar sorteo con secuencia de dramatismo para los resultados
const realizarSorteoForm = document.getElementById('realizarSorteoForm');
if (realizarSorteoForm) {
    realizarSorteoForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        // Fetch para obtener los resultados del sorteo
        fetch('/sorteos/realizar-sorteo', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Parsear el HTML para extraer ganadores y "al agua"
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(data.html, 'text/html');

                    // Extraer participantes "al agua"
                    const alAguaHeader = Array.from(doc.querySelectorAll('h3'))
                        .find(h => h.textContent.includes("Al Agua"));
                    const alAguaElements = alAguaHeader ? alAguaHeader.nextElementSibling.querySelectorAll('li') : [];
                    const alAgua = Array.from(alAguaElements).map(el => el.textContent.trim());

                    // Extraer ganadores
                    const ganadoresHeader = Array.from(doc.querySelectorAll('h3'))
                        .find(h => h.textContent.includes("Ganador(es)"));
                    const ganadorElements = ganadoresHeader ? ganadoresHeader.nextElementSibling.querySelectorAll('li') : [];
                    const ganadores = Array.from(ganadorElements).map(el => el.textContent.trim());

                    // Mostrar mensaje inicial de cuenta regresiva
                    Swal.fire({
                        title: '¡El sorteo comenzará pronto!',
                        html: 'Comenzando en <b>3</b> segundos...',
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: () => {
                            Swal.showLoading();
                            const countdownElem = Swal.getHtmlContainer().querySelector('b');
                            let countdown = 3;
                            const interval = setInterval(() => {
                                countdownElem.textContent = countdown;
                                countdown--;
                            }, 1000);
                            setTimeout(() => clearInterval(interval), 3000); // Limpiar intervalo al finalizar
                        }
                    }).then(() => {
                        // Mostrar los "al agua" con pausa de suspenso
                        let delay = 0;
                        alAgua.forEach((participante) => {
                            delay += 3000;
                            setTimeout(() => {
                                Swal.fire({
                                    title: 'Al Agua:',
                                    text: `${participante}`,
                                    icon: 'info',
                                    timer: 2500,
                                    showConfirmButton: false,
                                });
                            }, delay);
                        });

                        // Mostrar el ganador y lanzar el confetti de inmediato
                        delay += 3000;
                        setTimeout(() => {
                            Swal.fire({
                                title: '¡El ganador es!',
                                text: `${ganadores[0]}`,
                                icon: 'success',
                                showConfirmButton: true,
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                            lanzarConfetti();
                        }, delay);
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            })
            .catch(error => {
                console.error("Error en la solicitud del sorteo:", error);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al realizar el sorteo.' });
            });
    });
}

// Función para lanzar el confetti
function lanzarConfetti() {
    const duration = 15 * 100; // duración en milisegundos
    const animationEnd = Date.now() + duration;
    const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

    function randomInRange(min, max) {
        return Math.random() * (max - min) + min;
    }

    const interval = setInterval(function () {
        const timeLeft = animationEnd - Date.now();

        if (timeLeft <= 0) {
            return clearInterval(interval);
        }

        const particleCount = 50 * (timeLeft / duration);

        // Configuración de confetti desde dos posiciones de origen diferentes
        confetti(Object.assign({}, defaults, {
            particleCount,
            origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }
        }));
        confetti(Object.assign({}, defaults, {
            particleCount,
            origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
        }));
    }, 250);
}




// Manejar la anulación de ganador
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('anular-ganador')) {
        e.preventDefault();
        const participanteId = e.target.getAttribute('data-participante-id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esto anulará al ganador y seleccionará un nuevo ganador.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, reemplazar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                fetch('/sorteos/anular-ganador', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ sorteo_id: sorteoId, participante_id: participanteId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('¡Reemplazado!', data.message, 'success').then(() => location.reload());
                        } else {
                            mostrarAlerta('error', 'Error', data.message);
                        }
                    })
                    .catch(() => mostrarAlerta('error', 'Error', 'Ha ocurrido un error.'));
            }
        });
    }
});

// Función para verificar la existencia de un sorteo antes de la edición
function verificarSorteoExistente(event) {
    event.preventDefault();
    const sorteoId = document.getElementById('sorteo_id').value;

    fetch(`/sorteos/verificar/${sorteoId}`, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                window.location.href = `/sorteos/editar/${sorteoId}`;
            } else {
                mostrarAlerta('error', 'Sorteo no encontrado', 'El sorteo seleccionado no existe o fue eliminado.');
            }
        })
        .catch(() => mostrarAlerta('error', 'Error', 'No se pudo verificar el sorteo. No existe o hubo un error.'));
}

// Agregar el event listener al formulario de edición de sorteos
const editarSorteoForm = document.getElementById('editarSorteoForm');
if (editarSorteoForm) {
    editarSorteoForm.addEventListener('submit', verificarSorteoExistente);
}


// Manejar la publicación del sorteo
document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'publicarSorteo') {
        e.preventDefault();
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esto hará público el resultado del sorteo.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, publicar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/sorteos/publicar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ sorteo_id: sorteoId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('¡Publicado!', data.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Ha ocurrido un error.', 'error');
                    });
            }
        });
    }

    if (e.target && e.target.id === 'despublicarSorteo') {
        e.preventDefault();
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esto ocultará el resultado del sorteo al público.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, despublicar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/sorteos/despublicar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ sorteo_id: sorteoId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('¡Despublicado!', data.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Ha ocurrido un error.', 'error');
                    });
            }
        });
    }
});