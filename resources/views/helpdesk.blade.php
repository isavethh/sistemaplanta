{{--
    Vista del Widget de Helpdesk
    
    Generada automáticamente por: php artisan helpdeskwidget:install
    
    Personaliza esta vista según las necesidades de tu proyecto.
    Compatible con AdminLTE v3.
--}}
@extends('adminlte::page')

@section('title', 'Centro de Soporte')

@section('content_header')
    <h1><i class="fas fa-headset mr-2"></i>HelpDesk SaaS - Centro de Soporte</h1>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div id="helpdesk-widget-wrapper" style="width: 100%;">
                    <x-helpdesk-widget width="100%" />
                </div>
            </div>
        </div>
    </div>

    <style>
        #helpdesk-widget-wrapper iframe {
            width: 100% !important;
            border: none !important;
            display: block;
            min-height: 500px;
            transition: height 0.3s ease;
        }
    </style>

    <script>
        (function() {
            'use strict';

            console.log('🔍 [PARENT] Escuchando mensajes del widget');

            // Escuchar mensajes del iframe para redimensionar
            window.addEventListener('message', function(event) {
                if (event.data.type === 'widget-resize') {
                    const iframe = document.querySelector('#helpdesk-widget-wrapper iframe');
                    if (iframe) {
                        const newHeight = event.data.height;
                        console.log('📏 [PARENT] Recibido mensaje de resize:', newHeight);
                        iframe.style.height = newHeight + 'px';
                        console.log('✅ [PARENT] Altura actualizada a:', newHeight);
                    }
                }
            });

            console.log('✅ [PARENT] Listener de postMessage configurado');
        })();
    </script>
@endsection