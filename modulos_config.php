<?php
session_start();
include('inc/control.php');
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
$puede_editar = puede('configuracion', 'editar');
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Módulos y permisos</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/assets/css/escritorio.css?v=<?= filemtime(__DIR__ . '/assets/css/escritorio.css') ?>">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
</head>

<body class="esc">
<?php menu_v2('8'); ?>

<main class="esc-main">
    <div class="esc-title">
        <div>
            <div class="fecha">Configuración</div>
            <h1>Módulos y permisos</h1>
        </div>
    </div>

    <div class="esc-tabs" role="tablist">
        <button type="button" class="on" data-tab="modulos" role="tab">Módulos</button>
        <button type="button" data-tab="roles" role="tab">Roles y permisos</button>
    </div>

    <!-- Módulos -->
    <section id="tab-modulos" class="esc-card">
        <div class="esc-head">
            <span class="esc-chip" style="background:oklch(0.94 0.05 145);color:oklch(0.55 0.14 145)"><i class="ph-duotone ph-squares-four"></i></span>
            <h2>Módulos del sistema</h2>
            <span class="esc-meta" id="mod-meta"></span>
        </div>
        <div class="esc-note">Un módulo apagado desaparece del menú y su acceso se bloquea para todos. No se pierde ningún dato. Si activas uno, se activan también los que necesita.</div>
        <div id="mod-lista" class="esc-list" style="gap:0"></div>
    </section>

    <!-- Roles -->
    <section id="tab-roles" class="esc-roles esc-hide">
        <div class="esc-rolelist">
            <div id="rol-lista" style="display:flex;flex-direction:column;gap:6px"></div>
            <?php if ($puede_editar): ?><button type="button" class="esc-btn" id="rol-nuevo"><i class="ph ph-plus"></i> Nuevo rol</button><?php endif; ?>
        </div>
        <div class="esc-card esc-editor" id="rol-editor"></div>
    </section>
</main>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script>
(function(){
    var API = '/inc/modulos_api.php', CSRF = <?= json_encode($_SESSION['csrf']) ?>, EDITA = <?= $puede_editar ? 'true' : 'false' ?>;
    var ACCIONES = [['ver','Ver'],['crear','Crear'],['editar','Editar'],['anular','Anular']];
    var S = {modulos: [], roles: [], rolSel: null, nuevo: false, borrador: null};

    function esc(t){ var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; }
    function toast(msg, err){
        var t = document.createElement('div'); t.className = 'esc-toast' + (err ? ' err' : ''); t.textContent = msg;
        document.body.appendChild(t); setTimeout(function(){ t.remove(); }, err ? 4500 : 2500);
    }
    function llamar(accion, datos){
        var fd = new FormData(); fd.append('accion', accion);
        Object.keys(datos || {}).forEach(function(k){ fd.append(k, datos[k]); });
        return fetch(API, {method:'POST', body:fd, headers:{'X-CSRF-Token': CSRF, 'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin'})
            .then(function(r){ return r.json(); })
            .then(function(j){ if (!j.respuesta) { toast(j.mensaje || 'Error', true); return null; } cargar(j); return j; })
            .catch(function(){ toast('No se pudo conectar', true); return null; });
    }
    function cargar(j){
        S.modulos = j.modulos; S.roles = j.roles;
        if (S.rolSel && !S.roles.some(function(r){ return r.id_rol === S.rolSel; })) S.rolSel = null;
        if (!S.rolSel && !S.nuevo && S.roles.length) { var primero = S.roles.filter(function(r){ return !r.es_admin; })[0] || S.roles[0]; S.rolSel = primero.id_rol; }
        pintar();
    }

    function tono(m){ return 'background:oklch(0.94 0.05 '+m.tono+');color:oklch(0.55 0.15 '+m.tono+')'; }
    function nombreMod(clave){ var m = S.modulos.filter(function(x){ return x.clave === clave; })[0]; return m ? m.nombre : clave; }

    function pintarModulos(){
        var act = S.modulos.filter(function(m){ return m.efectivo; }).length;
        document.getElementById('mod-meta').textContent = act + ' de ' + S.modulos.length + ' activos';
        document.getElementById('mod-lista').innerHTML = S.modulos.map(function(m){
            var deps = (m.depende || '').split(',').map(function(d){ return d.trim(); }).filter(Boolean).map(nombreMod);
            var estado = '';
            if (m.nucleo) estado = '<span class="esc-status">Siempre activo</span>';
            else if (m.activo && !m.efectivo) estado = '<span class="esc-status amber">Detenido: falta un módulo requerido</span>';
            return '<div class="esc-mod'+(m.efectivo ? '' : ' off')+'">'+
                '<span class="esc-chip" style="'+tono(m)+'"><i class="ph-duotone '+esc(m.icono)+'"></i></span>'+
                '<div class="mid"><div class="nm">'+esc(m.nombre)+'</div><div class="ds">'+esc(m.descripcion)+'</div>'+
                (deps.length ? '<div class="rq">Requiere: '+esc(deps.join(', '))+'</div>' : '')+'</div>'+
                estado+
                '<label class="esc-switch" title="'+(m.activo ? 'Apagar' : 'Activar')+'"><input type="checkbox" data-mod="'+m.id_modulo+'" '+(m.activo ? 'checked' : '')+' '+((m.nucleo || !EDITA) ? 'disabled' : '')+' aria-label="'+esc(m.nombre)+'"><i></i></label>'+
            '</div>';
        }).join('');
    }

    function rolActual(){
        if (S.nuevo) return S.borrador;
        return S.roles.filter(function(r){ return r.id_rol === S.rolSel; })[0] || null;
    }
    function pintarRoles(){
        document.getElementById('rol-lista').innerHTML = S.roles.map(function(r){
            return '<button type="button" class="esc-roleitem'+(!S.nuevo && r.id_rol === S.rolSel ? ' on' : '')+'" data-rol="'+r.id_rol+'">'+
                '<i class="ph-duotone '+(r.es_admin ? 'ph-shield-star' : 'ph-user-circle')+'" style="font-size:18px"></i> '+esc(r.nombre)+'<small>'+r.usuarios+' usuario'+(r.usuarios === 1 ? '' : 's')+'</small></button>';
        }).join('');
        var r = rolActual(), ed = document.getElementById('rol-editor');
        if (!r) { ed.innerHTML = '<div class="esc-empty">Crea un rol para empezar.</div>'; return; }
        var soloLectura = !EDITA || r.es_admin;
        var filas = S.modulos.map(function(m){
            var p = (r.permisos && r.permisos[m.clave]) || {};
            var celdas = ACCIONES.map(function(a){
                return '<td><input type="checkbox" data-m="'+esc(m.clave)+'" data-a="'+a[0]+'" '+(r.es_admin || p[a[0]] ? 'checked' : '')+' '+(soloLectura ? 'disabled' : '')+' aria-label="'+esc(m.nombre+' '+a[1])+'"></td>';
            }).join('');
            return '<tr class="'+(m.efectivo ? '' : 'off')+'"><td><span class="mn"><span class="esc-chip" style="'+tono(m)+'"><i class="ph-duotone '+esc(m.icono)+'"></i></span>'+esc(m.nombre)+(m.efectivo ? '' : ' <span class="esc-status">apagado</span>')+'</span></td>'+celdas+'</tr>';
        }).join('');
        ed.innerHTML =
            '<div class="esc-head"><span class="esc-chip" style="background:oklch(0.94 0.05 230);color:oklch(0.55 0.15 230)"><i class="ph-duotone ph-shield-check"></i></span><h2>'+(S.nuevo ? 'Nuevo rol' : esc(r.nombre))+'</h2></div>'+
            (r.es_admin ? '<div class="esc-callout">El Administrador tiene acceso total a todos los módulos activos y no se puede modificar.</div>' : '')+
            '<div class="esc-row"><div class="esc-field"><label for="rol-nombre">Nombre</label><input id="rol-nombre" maxlength="40" value="'+esc(r.nombre)+'" '+(soloLectura ? 'disabled' : '')+'></div>'+
            '<div class="esc-field" style="flex-basis:320px"><label for="rol-desc">Descripción</label><input id="rol-desc" maxlength="160" value="'+esc(r.descripcion)+'" '+(soloLectura ? 'disabled' : '')+'></div></div>'+
            '<div class="esc-scroll"><table class="esc-matrix"><thead><tr><th>Módulo</th>'+ACCIONES.map(function(a){ return '<th>'+a[1]+'</th>'; }).join('')+'</tr></thead><tbody>'+filas+'</tbody></table></div>'+
            '<div class="esc-note">«Ver» da acceso al módulo; sin «Ver» no se aplican las demás acciones.</div>'+
            (soloLectura ? '' : '<div class="esc-actions">'+
                (S.nuevo ? '<button type="button" class="esc-btn" id="rol-cancelar">Cancelar</button>' : '<button type="button" class="esc-btn danger" id="rol-borrar">Borrar rol</button>')+
                '<button type="button" class="esc-btn primary" id="rol-guardar">Guardar</button></div>');
    }
    function pintar(){ pintarModulos(); pintarRoles(); }

    function leerPermisos(){
        var p = {};
        document.querySelectorAll('#rol-editor input[data-m]').forEach(function(c){
            var k = c.getAttribute('data-m'), a = c.getAttribute('data-a');
            p[k] = p[k] || {}; p[k][a] = c.checked ? 1 : 0;
        });
        return p;
    }
    document.addEventListener('click', function(e){
        var t = e.target.closest('button'); if (!t) return;
        if (t.getAttribute('data-tab')) {
            document.querySelectorAll('.esc-tabs button').forEach(function(b){ b.classList.toggle('on', b === t); });
            document.getElementById('tab-modulos').classList.toggle('esc-hide', t.dataset.tab !== 'modulos');
            document.getElementById('tab-roles').classList.toggle('esc-hide', t.dataset.tab !== 'roles');
        } else if (t.getAttribute('data-rol')) {
            S.nuevo = false; S.rolSel = parseInt(t.getAttribute('data-rol'), 10); pintarRoles();
        } else if (t.id === 'rol-nuevo') {
            S.nuevo = true; S.borrador = {id_rol: 0, nombre: '', descripcion: '', es_admin: 0, permisos: {}}; pintarRoles();
            document.getElementById('rol-nombre').focus();
        } else if (t.id === 'rol-cancelar') {
            S.nuevo = false; pintarRoles();
        } else if (t.id === 'rol-guardar') {
            var r = rolActual(); if (!r) return;
            t.disabled = true;
            llamar('guardar_rol', {id: S.nuevo ? 0 : r.id_rol, nombre: document.getElementById('rol-nombre').value, descripcion: document.getElementById('rol-desc').value, permisos: JSON.stringify(leerPermisos())})
                .then(function(j){ t.disabled = false; if (j) { S.nuevo = false; S.rolSel = j.id; pintar(); toast('Rol guardado'); } });
        } else if (t.id === 'rol-borrar') {
            var r2 = rolActual(); if (!r2 || !confirm('¿Borrar el rol «'+r2.nombre+'»?')) return;
            llamar('borrar_rol', {id: r2.id_rol}).then(function(j){ if (j) { S.rolSel = null; pintar(); toast('Rol borrado'); } });
        }
    });
    document.addEventListener('change', function(e){
        var c = e.target;
        if (c.matches('input[data-mod]')) {
            llamar('modulo_activo', {id: c.getAttribute('data-mod'), activo: c.checked ? 1 : 0}).then(function(j){
                if (!j) { pintarModulos(); return; }
                var extra = (j.tambien_activados || []).map(nombreMod);
                toast(extra.length ? 'Módulo activado. También se activó: ' + extra.join(', ') : (c.checked ? 'Módulo activado' : 'Módulo apagado'));
            });
        } else if (c.matches('#rol-editor input[data-a]')) {
            // «Ver» desmarcado limpia las demás; marcar cualquier acción exige «Ver».
            var fila = c.closest('tr'), ver = fila.querySelector('input[data-a="ver"]');
            if (c.getAttribute('data-a') === 'ver' && !c.checked) fila.querySelectorAll('input').forEach(function(i){ i.checked = false; });
            else if (c.checked) ver.checked = true;
        }
    });

    fetch(API + '?accion=listar', {credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){ return r.json(); }).then(function(j){ if (j.respuesta) cargar(j); else toast(j.mensaje || 'Error', true); });
})();
</script>
</body>
</html>
