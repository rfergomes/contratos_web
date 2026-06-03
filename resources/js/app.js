import './bootstrap';

// Import Bootstrap e expõe globalmente para os modais nas views
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Import jQuery e expõe globalmente (exigido por Toastr)
import $ from 'jquery';
window.$ = window.jQuery = $;

// Import Toastr e expõe globalmente
import toastr from 'toastr';
window.toastr = toastr;

// Import SweetAlert2 e expõe globalmente
import Swal from 'sweetalert2';
window.Swal = Swal;

// Import AdminLTE 4
import 'admin-lte/dist/js/adminlte.min.js';

// Configurações padrão do Toastr
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};