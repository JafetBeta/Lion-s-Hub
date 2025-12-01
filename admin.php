<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión y Seguimiento de Pagos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>
    <style>
        :root { --primary-color: #ff0000; --secondary-color: #000000; --accent-color1: #ff6a00; --accent-color2: #ff3c00; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(to right, var(--primary-color), var(--secondary-color)); }
        .btn-gradient { background: linear-gradient(to right, var(--accent-color1), var(--accent-color2)); color: white; transition: all 0.3s ease; }
        .btn-gradient:hover { box-shadow: 0 4px 10px rgba(255, 60, 0, 0.4); }
        .modal-overlay { background-color: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px); }
    </style>
</head>
<body class="p-4 sm:p-6 lg:p-8">

    <div class="text-white text-center mb-8">
        <h1 class="text-3xl sm:text-4xl font-bold">Gestión y seguimiento de pagos</h1>
    </div>

    <div class="max-w-7xl mx-auto bg-white p-6 sm:p-8 rounded-xl shadow-lg">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-gray-50 p-6 rounded-xl shadow-md"><p class="text-lg font-medium text-gray-700">Total de Estudiantes</p><span id="totalStudentsCard" class="text-4xl font-bold text-gray-900">0</span></div>
            <div class="bg-gray-50 p-6 rounded-xl shadow-md"><p class="text-lg font-medium text-gray-700">Boletos Pagados</p><span id="paidTicketsCard" class="text-4xl font-bold text-gray-900">0</span></div>
            <div class="bg-gray-50 p-6 rounded-xl shadow-md"><p class="text-lg font-medium text-gray-700">Total de Ingresos</p><span id="totalRevenueCard" class="text-4xl font-bold text-gray-900">MXN 0.00</span></div>
        </div>

        <div class="p-6 bg-white rounded-xl shadow-lg mb-10 border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-5">Filtros y Búsqueda</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div><label for="searchInput" class="block text-sm font-medium text-gray-700 mb-1">Buscar por Nombre/Matrícula:</label><input type="text" id="searchInput" class="w-full p-3 border border-gray-300 rounded-lg"></div>
                <div><label for="paymentStatusFilter" class="block text-sm font-medium text-gray-700 mb-1">Estado de Pago:</label><select id="paymentStatusFilter" class="w-full p-3 border border-gray-300 rounded-lg"><option value="todos">Todos</option><option value="pagado">Pagado</option><option value="pendiente">Pendiente</option></select></div>
                <div><label for="careerFilter" class="block text-sm font-medium text-gray-700 mb-1">Carrera:</label><select id="careerFilter" class="w-full p-3 border border-gray-300 rounded-lg"><option value="todos">Todas</option></select></div>
                <button id="searchButton" class="w-full sm:w-auto text-white font-bold py-3 px-6 rounded-lg shadow-md btn-gradient">Buscar</button>
            </div>
        </div>

        <div class="p-6 bg-white rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-5">Lista de Estudiantes</h2>
            <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Matrícula</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Nombre</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Grupo</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Boletos (Total)</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Estado</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Último Pago</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Boletos x Destino</th></tr></thead>
                    <tbody id="studentListTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                </table>
                <div id="noResultsMessage" class="text-center py-4 text-gray-600 hidden">No se encontraron estudiantes.</div>
            </div>
        </div>
    </div>

    <div id="userDetailModal" class="fixed inset-0 flex items-center justify-center modal-overlay hidden z-50">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-11/12 max-w-2xl transform transition-all duration-300" id="modalContent">
            <div class="flex justify-center mb-6"><div id="qrcodeContainer" class="p-2 border border-gray-300 rounded-lg bg-white"></div></div>
            <div class="flex justify-between items-center mb-6"><h3 class="text-2xl font-bold text-gray-900" id="modalUserName"></h3><button id="closeModalButton" class="text-gray-500 hover:text-gray-800"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button></div>
            <div id="cobroPresencialSection" class="mb-6 hidden"><h4 class="text-xl font-semibold text-gray-800 mb-3">Cobro Presencial</h4><div class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="w-full"><label for="ticketsCountInput" class="block text-sm font-medium text-gray-700 mb-1">Cantidad:</label><input type="number" id="ticketsCountInput" value="1" min="1" class="w-full p-3 border rounded-lg"></div>
                <div class="w-full"><label for="ticketsDestinationSelect" class="block text-sm font-medium text-gray-700 mb-1">Destino:</label><select id="ticketsDestinationSelect" class="w-full p-3 border rounded-lg"><option value="Matamoros">Matamoros</option><option value="Valle Hermoso">Valle Hermoso</option></select></div>
                <button id="chargeButton" class="w-full sm:w-auto font-bold py-3 px-6 rounded-lg btn-gradient">Cobrar</button>
            </div></div>
            <div class="mb-6"><h4 class="text-xl font-semibold text-gray-800 mb-3">Datos Personales</h4><p><span class="font-medium">Matrícula:</span> <span id="modalMatricula"></span></p><p><span class="font-medium">Carrera:</span> <span id="modalCarrera"></span></p><p><span class="font-medium">Teléfono:</span> <span id="modalTelefono"></span></p></div>
            <div><h4 class="text-xl font-semibold text-gray-800 mb-3">Historial de Boletos</h4><div id="modalTicketsDetail" class="space-y-3"></div><p id="noTicketsMessage" class="text-gray-600 mt-4 hidden">Este estudiante no tiene boletos pagados.</p></div>
        </div>
    </div>

    <script>
        const TICKET_PRICE_MATAMOROS = 10;
        const TICKET_PRICE_VALLE_HERMOSO = 20;

        const totalStudentsCard = document.getElementById('totalStudentsCard'), paidTicketsCard = document.getElementById('paidTicketsCard'), totalRevenueCard = document.getElementById('totalRevenueCard');
        const searchInput = document.getElementById('searchInput'), searchButton = document.getElementById('searchButton');
        const paymentStatusFilter = document.getElementById('paymentStatusFilter'), careerFilter = document.getElementById('careerFilter');
        const studentListTableBody = document.getElementById('studentListTableBody'), noResultsMessage = document.getElementById('noResultsMessage');
        const userDetailModal = document.getElementById('userDetailModal'), modalContent = document.getElementById('modalContent'), closeModalButton = document.getElementById('closeModalButton');
        const modalUserName = document.getElementById('modalUserName'), modalMatricula = document.getElementById('modalMatricula'), modalCarrera = document.getElementById('modalCarrera');
        const modalTelefono = document.getElementById('modalTelefono'), modalTicketsDetail = document.getElementById('modalTicketsDetail'), noTicketsMessage = document.getElementById('noTicketsMessage');
        const cobroPresencialSection = document.getElementById('cobroPresencialSection'), ticketsCountInput = document.getElementById('ticketsCountInput');
        const ticketsDestinationSelect = document.getElementById('ticketsDestinationSelect'), chargeButton = document.getElementById('chargeButton');
        const qrcodeContainer = document.getElementById('qrcodeContainer');
        let currentStudentMatricula = null;
        let processedPeopleData = [], currentFilteredData = [];

        async function fetchDataFromServer() {
            try {
                const response = await fetch('admin_api/obtener_datos.php');
                const result = await response.json();
                if (result.success) {
                    processedPeopleData = result.data;
                    currentFilteredData = processedPeopleData;
                    populateCareerFilter();
                    renderDashboard(currentFilteredData);
                } else { alert('Error al cargar los datos: ' + result.message); }
            } catch (error) { console.error('Error de conexión:', error); alert('Hubo un problema de conexión con el servidor.'); }
        }

        document.addEventListener('DOMContentLoaded', fetchDataFromServer);

        function renderDashboard(dataToRender) {
            const totalStudents = processedPeopleData.length;
            const totalPaidTicketsOverall = processedPeopleData.reduce((sum, p) => sum + p.boletosPagadosCount, 0);
            const totalRevenueOverall = processedPeopleData.reduce((sum, p) => sum + p.totalRevenue, 0);
            totalStudentsCard.textContent = totalStudents;
            paidTicketsCard.textContent = totalPaidTicketsOverall;
            totalRevenueCard.textContent = `MXN ${totalRevenueOverall.toFixed(2)}`;

            studentListTableBody.innerHTML = '';
            noResultsMessage.classList.toggle('hidden', dataToRender.length > 0);

            dataToRender.forEach(person => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 cursor-pointer';
                row.dataset.matricula = person.matricula;
                
                let boletosMatamoros = 0, boletosValleHermoso = 0;
                person.boletos.forEach(b => {
                    if (b.destino === 'Matamoros') boletosMatamoros += b.cantidad;
                    if (b.destino === 'Valle Hermoso') boletosValleHermoso += b.cantidad;
                });
                const destinos = [];
                if (boletosMatamoros > 0) destinos.push(`Mat: ${boletosMatamoros}`);
                if (boletosValleHermoso > 0) destinos.push(`V.H.: ${boletosValleHermoso}`);
                const destinoDetallado = destinos.length > 0 ? destinos.join(', ') : '-';

                row.innerHTML = `
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">${person.matricula}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">${person.nombre}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">${person.grupo || '-'}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">${person.boletosPagadosCount}</td>
                    <td class="px-6 py-4 text-sm"><span class="px-2 text-xs font-semibold rounded-full ${person.estadoGeneral === 'Pagado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${person.estadoGeneral}</span></td>
                    <td class="px-6 py-4 text-sm text-gray-700">${person.ultimaFechaPago}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">${destinoDetallado}</td>
                `;
                studentListTableBody.appendChild(row);
            });
        }

        function handleSearchAndFilter() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const filterStatus = paymentStatusFilter.value;
            const filterCareer = careerFilter.value;
            currentFilteredData = processedPeopleData.filter(person => 
                (person.nombre.toLowerCase().includes(searchTerm) || person.matricula.toLowerCase().includes(searchTerm)) &&
                (filterStatus === 'todos' || person.estadoGeneral.toLowerCase() === filterStatus) &&
                (filterCareer === 'todos' || person.carrera === filterCareer)
            );
            renderDashboard(currentFilteredData);
        }

        async function handlePresencialCharge() {
            if (!currentStudentMatricula) return alert("Error: No se ha seleccionado un estudiante.");
            const formData = new FormData();
            formData.append('matricula', currentStudentMatricula);
            formData.append('cantidad', ticketsCountInput.value);
            formData.append('destino', ticketsDestinationSelect.value);
            
            chargeButton.disabled = true;
            chargeButton.textContent = 'Procesando...';
            try {
                const response = await fetch('admin_api/registrar_pago.php', { method: 'POST', body: formData });
                const result = await response.json();
                alert(result.message);
                if (result.success) {
                    hideUserDetailModal();
                    await fetchDataFromServer();
                }
            } catch (error) { alert('Hubo un problema de conexión al registrar el pago.'); }
            finally { chargeButton.disabled = false; chargeButton.textContent = 'Cobrar'; }
        }

        function populateCareerFilter() {
            const careers = [...new Set(processedPeopleData.map(p => p.carrera))].filter(Boolean).sort();
            careerFilter.innerHTML = '<option value="todos">Todas</option>';
            careers.forEach(c => careerFilter.innerHTML += `<option value="${c}">${c}</option>`);
        }
        
        function showUserDetailModal(person) {
            currentStudentMatricula = person.matricula;
            modalUserName.textContent = person.nombre;
            modalMatricula.textContent = person.matricula;
            modalCarrera.textContent = person.carrera || 'No especificada';
            modalTelefono.textContent = person.telefono || 'No especificado';
            cobroPresencialSection.classList.toggle('hidden', person.estadoGeneral !== 'Pendiente');
            
            modalTicketsDetail.innerHTML = '';
            noTicketsMessage.classList.toggle('hidden', person.boletos.length > 0);
            person.boletos.forEach(boleto => {
                const ingreso = boleto.destino === 'Matamoros' ? boleto.cantidad * TICKET_PRICE_MATAMOROS : boleto.cantidad * TICKET_PRICE_VALLE_HERMOSO;
                modalTicketsDetail.innerHTML += `<div class="bg-gray-50 p-4 rounded-lg border"><p class="text-sm"><span class="font-medium">Fecha:</span> ${new Date(boleto.fechaHoraCompra).toLocaleString()}</p><p class="text-sm"><span class="font-medium">Destino:</span> ${boleto.destino} | <span class="font-medium">Cantidad:</span> ${boleto.cantidad} | <span class="font-medium">Ingreso:</span> MXN ${ingreso.toFixed(2)}</p></div>`;
            });
            
            qrcodeContainer.innerHTML = '';
            QRCode.toCanvas(qrcodeContainer, JSON.stringify({ matricula: person.matricula, nombre: person.nombre }), { width: 180 }, err => { if (err) console.error(err); });
            
            userDetailModal.classList.remove('hidden');
        }

        function hideUserDetailModal() { userDetailModal.classList.add('hidden'); }

        searchButton.addEventListener('click', handleSearchAndFilter);
        chargeButton.addEventListener('click', handlePresencialCharge);
        studentListTableBody.addEventListener('click', e => {
            const row = e.target.closest('tr');
            if (row) {
                const person = processedPeopleData.find(p => p.matricula === row.dataset.matricula);
                if (person) showUserDetailModal(person);
            }
        });
        closeModalButton.addEventListener('click', hideUserDetailModal);
        userDetailModal.addEventListener('click', e => { if (e.target.id === 'userDetailModal') hideUserDetailModal(); });
    </script>
</body>
</html>