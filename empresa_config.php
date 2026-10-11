<?php
session_start();
include('inc/control.php');
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
$puede_editar = puede('configuracion', 'editar');
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empresa y medios de pago</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/assets/css/escritorio.css?v=<?= filemtime(__DIR__ . '/assets/css/escritorio.css') ?>">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
</head>

<body class="esc">
<?php menu_v2('8'); ?>

<main class="esc-main">
    <div class="esc-title">
        <div>
            <div class="fecha">Configuración</div>
            <h1>Empresa y medios de pago</h1>
        </div>
    </div>

    <div class="esc-tabs" role="tablist">
        <button type="button" class="on" data-tab="empresa" role="tab">Datos de la empresa</button>
        <button type="button" data-tab="medios" role="tab">Medios de pago</button>
    </div>

    <!-- Empresa -->
    <section id="tab-empresa" class="esc-row" style="align-items:flex-start">
        <div class="esc-card" style="flex:2 1 460px">
            <div class="esc-head">
                <span class="esc-chip" style="background:oklch(0.94 0.05 230);color:oklch(0.55 0.15 230)"><i class="ph-duotone ph-buildings"></i></span>
                <h2>Datos de la empresa</h2>
            </div>
            <div class="esc-note">Salen en recibos, tickets, boletas de pago y en el nombre del sistema.</div>
            <div class="esc-row">
                <div class="esc-field"><label for="empresa_nombre">Nombre comercial *</label><input id="empresa_nombre" maxlength="80"></div>
                <div class="esc-field"><label for="empresa_razon">Razón social</label><input id="empresa_razon" maxlength="120"></div>
            </div>
            <div class="esc-row">
                <div class="esc-field"><label for="empresa_ruc">RUC</label><input id="empresa_ruc" maxlength="15" inputmode="numeric"></div>
                <div class="esc-field"><label for="empresa_telefono">Teléfono</label><input id="empresa_telefono" maxlength="40"></div>
                <div class="esc-field"><label for="empresa_email">Correo</label><input id="empresa_email" maxlength="80" type="email"></div>
            </div>
            <div class="esc-row">
                <div class="esc-field" style="flex-basis:100%"><label for="empresa_direccion">Dirección</label><input id="empresa_direccion" maxlength="160"></div>
            </div>
            <div class="esc-row">
                <div class="esc-field"><label for="empresa_lema">Frase bajo el nombre (opcional)</label><input id="empresa_lema" maxlength="160" placeholder="Ej.: Insumos para la industria conservera"></div>
                <div class="esc-field"><label for="empresa_gracias">Mensaje al pie del recibo</label><input id="empresa_gracias" maxlength="120"></div>
            </div>
        </div>

        <div class="esc-card" style="flex:1 1 340px">
            <div class="esc-head">
                <span class="esc-chip" style="background:oklch(0.94 0.05 37);color:oklch(0.58 0.18 37)"><i class="ph-duotone ph-image"></i></span>
                <h2>Marca</h2>
            </div>
            <div class="esc-note">Aparece arriba a la izquierda y en la pantalla de ingreso. Puedes usar texto o subir una imagen.</div>
            <div class="esc-seg" id="marca-modo" style="align-self:flex-start">
                <button type="button" data-modo="texto" class="on">Texto</button>
                <button type="button" data-modo="imagen">Imagen</button>
            </div>
            <div id="marca-texto" class="esc-row">
                <div class="esc-field"><label for="marca_linea1">Línea pequeña (opcional)</label><input id="marca_linea1" maxlength="30" placeholder="GRUPO"></div>
                <div class="esc-field"><label for="marca_linea2">Línea principal</label><input id="marca_linea2" maxlength="30" placeholder="MI EMPRESA"></div>
            </div>
            <div id="marca-imagen" class="esc-hide" style="display:flex;flex-direction:column;gap:10px">
                <div class="logo-prev" id="logo-prev"><span class="esc-note">Sin imagen</span></div>
                <div class="esc-actions" style="justify-content:flex-start">
                    <?php if ($puede_editar): ?>
                    <label class="esc-btn" style="margin:0;cursor:pointer"><i class="ph ph-upload-simple"></i> Subir imagen<input type="file" id="logo-file" accept="image/png,image/jpeg,image/webp" style="display:none"></label>
                    <button type="button" class="esc-btn danger" id="logo-quitar">Quitar</button>
                    <?php endif; ?>
                </div>
                <div class="esc-note">PNG, JPG o WEBP, hasta 1.5 MB. Mejor con fondo transparente y horizontal.</div>
            </div>
            <div class="esc-note" style="margin-top:4px">Vista previa</div>
            <div class="marca-prev"><div class="esc-brand" id="marca-prev"></div></div>
        </div>
    </section>
    <div id="acciones-empresa" class="esc-actions"><?php if ($puede_editar): ?><button type="button" class="esc-btn primary" id="guardar-empresa">Guardar cambios</button><?php endif; ?></div>

    <!-- Medios de pago -->
    <section id="tab-medios" class="esc-card esc-hide">
        <div class="esc-head">
            <span class="esc-chip" style="background:oklch(0.94 0.05 300);color:oklch(0.55 0.15 300)"><i class="ph-duotone ph-credit-card"></i></span>
            <h2>Medios de pago</h2>
            <?php if ($puede_editar): ?><button type="button" class="esc-btn" id="medio-nuevo"><i class="ph ph-plus"></i> Nuevo medio</button><?php endif; ?>
        </div>
        <div class="esc-note">Son las formas de cobrar en Caja y de pagar gastos y compras. «Digital» cuenta el medio como pago electrónico en el Escritorio. Un medio con movimientos no se borra: desactívalo.</div>
        <div id="medios-lista"></div>
    </section>
</main>

<style>
    .logo-prev{border:1px dashed var(--border-strong);border-radius:12px;background:var(--soft);min-height:84px;display:flex;align-items:center;justify-content:center;padding:10px}
    .logo-prev img{max-height:64px;max-width:100%}
    .marca-prev{border:1px solid var(--border);border-bottom:3px solid var(--primary);border-radius:12px;background:#fff;padding:10px 16px;display:flex}
    .marca-prev .esc-brand{pointer-events:none}
    .medio-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-top:1px solid var(--row-line);flex-wrap:wrap}
    .medio-row:first-child{border-top:0}
    .medio-row .ico{width:34px;height:34px;border-radius:10px;background:var(--track);display:flex;align-items:center;justify-content:center;color:var(--ink-2)}
    .medio-row input.nm,.medio-row select{font-family:inherit;font-size:13.5px;border:1px solid var(--border-strong);border-radius:10px;padding:7px 10px;background:#fff;color:var(--ink)}
    .medio-row input.nm{flex:1 1 180px;min-width:140px}
    .medio-row label.chk{display:flex;align-items:center;gap:6px;font-size:12.5px;color:var(--ink-2);margin:0;font-weight:400}
    .medio-row.off .nm{color:var(--muted)}
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script>
(function(){
    var API = '/inc/empresa_api.php', CSRF = <?= json_encode($_SESSION['csrf']) ?>, EDITA = <?= $puede_editar ? 'true' : 'false' ?>;
    var CAMPOS = ['empresa_nombre','empresa_razon','empresa_ruc','empresa_telefono','empresa_email','empresa_direccion','empresa_lema','empresa_gracias','marca_linea1','marca_linea2'];
    var ICONOS = [['fa-money-bill-wave','Billete'],['fa-mobile-alt','Celular'],['fa-university','Banco'],['fa-credit-card','Tarjeta'],['fa-wallet','Billetera'],['fa-coins','Monedas'],['fa-qrcode','QR'],['fa-exchange-alt','Transferencia'],['fa-hand-holding-usd','Mano'],['fa-file-invoice','Documento']];
    var S = {empresa: {}, logo_url: null, medios: [], modo: 'texto'};

    function esc(t){ var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; }
    function toast(msg, err){
        var t = document.createElement('div'); t.className = 'esc-toast' + (err ? ' err' : ''); t.textContent = msg;
        document.body.appendChild(t); setTimeout(function(){ t.remove(); }, err ? 4500 : 2500);
    }
    function llamar(accion, datos, archivo){
        var fd = new FormData(); fd.append('accion', accion);
        Object.keys(datos || {}).forEach(function(k){ fd.append(k, datos[k]); });
        if (archivo) fd.append('logo', archivo);
        return fetch(API, {method:'POST', body:fd, headers:{'X-CSRF-Token': CSRF, 'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin'})
            .then(function(r){ return r.json(); })
            .then(function(j){ if (!j.respuesta) { toast(j.mensaje || 'Error', true); return null; } cargar(j); if (j.mensaje) toast(j.mensaje); return j; })
            .catch(function(){ toast('No se pudo conectar', true); return null; });
    }
    function cargar(j){
        S.empresa = j.empresa; S.logo_url = j.logo_url; S.medios = j.medios; S.modo = j.empresa.marca_modo === 'imagen' ? 'imagen' : 'texto';
        CAMPOS.forEach(function(c){ var el = document.getElementById(c); if (el && document.activeElement !== el) el.value = j.empresa[c] || ''; });
        pintarMarca(); pintarMedios();
    }

    function pintarMarca(){
        document.querySelectorAll('#marca-modo button').forEach(function(b){ b.classList.toggle('on', b.dataset.modo === S.modo); });
        document.getElementById('marca-texto').classList.toggle('esc-hide', S.modo !== 'texto');
        var im = document.getElementById('marca-imagen'); im.classList.toggle('esc-hide', S.modo !== 'imagen'); im.style.display = S.modo === 'imagen' ? 'flex' : 'none';
        document.getElementById('logo-prev').innerHTML = S.logo_url ? '<img src="'+esc(S.logo_url)+'" alt="Logo">' : '<span class="esc-note">Sin imagen</span>';
        var q = document.getElementById('logo-quitar'); if (q) q.style.display = S.logo_url ? '' : 'none';
        var p = document.getElementById('marca-prev');
        if (S.modo === 'imagen' && S.logo_url) { p.innerHTML = '<img class="esc-brand-img" src="'+esc(S.logo_url)+'" alt="">'; return; }
        var l1 = document.getElementById('marca_linea1').value.trim(), l2 = document.getElementById('marca_linea2').value.trim() || (l1 ? '' : document.getElementById('empresa_nombre').value.trim());
        p.innerHTML = (l1 ? '<span>'+esc(l1)+'</span>' : '') + '<b>'+esc(l2)+'</b>';
    }

    function pintarMedios(){
        document.getElementById('medios-lista').innerHTML = S.medios.filter(function(m){ return +m.selector === 1 || +m.sistema === 0; }).map(function(m){
            var opts = ICONOS.map(function(i){ return '<option value="'+i[0]+'"'+(i[0] === m.icono ? ' selected' : '')+'>'+i[1]+'</option>'; }).join('');
            var dis = EDITA ? '' : ' disabled';
            return '<div class="medio-row'+(+m.activo ? '' : ' off')+'" data-clave="'+esc(m.clave)+'">'+
                '<span class="ico"><i class="fas '+esc(m.icono)+'"></i></span>'+
                '<input class="nm" value="'+esc(m.nombre)+'" maxlength="40" aria-label="Nombre"'+dis+'>'+
                '<select class="ic" aria-label="Ícono"'+dis+'>'+opts+'</select>'+
                '<label class="chk"><input type="checkbox" class="dg"'+(+m.digital ? ' checked' : '')+dis+'> Digital</label>'+
                '<label class="chk"><input type="checkbox" class="ac"'+(+m.activo ? ' checked' : '')+((+m.sistema || !EDITA) ? ' disabled' : '')+'> Activo</label>'+
                (EDITA ? '<button type="button" class="esc-btn mg">Guardar</button>' : '')+
                (EDITA && !+m.sistema ? '<button type="button" class="esc-btn danger mb">Borrar</button>' : '')+
                (+m.sistema ? '<span class="esc-status">del sistema</span>' : '')+
            '</div>';
        }).join('');
    }

    document.addEventListener('click', function(e){
        var t = e.target.closest('button'); if (!t) return;
        if (t.dataset.tab) {
            document.querySelectorAll('.esc-tabs button').forEach(function(b){ b.classList.toggle('on', b === t); });
            document.getElementById('tab-empresa').classList.toggle('esc-hide', t.dataset.tab !== 'empresa');
            document.getElementById('acciones-empresa').classList.toggle('esc-hide', t.dataset.tab !== 'empresa');
            document.getElementById('tab-medios').classList.toggle('esc-hide', t.dataset.tab !== 'medios');
        } else if (t.dataset.modo) {
            S.modo = t.dataset.modo; pintarMarca();
        } else if (t.id === 'guardar-empresa') {
            var datos = {marca_modo: S.modo};
            CAMPOS.forEach(function(c){ datos[c] = document.getElementById(c).value; });
            t.disabled = true; llamar('guardar_empresa', datos).then(function(){ t.disabled = false; });
        } else if (t.id === 'logo-quitar') {
            if (confirm('¿Quitar la imagen y volver a la marca de texto?')) llamar('quitar_logo');
        } else if (t.id === 'medio-nuevo') {
            var nom = prompt('Nombre del nuevo medio de pago (ej.: Yape, Transferencia BCP):');
            if (nom && nom.trim()) llamar('medio_guardar', {nombre: nom.trim(), icono: 'fa-mobile-alt', digital: 1, activo: 1});
        } else if (t.classList.contains('mg')) {
            var row = t.closest('.medio-row');
            llamar('medio_guardar', {clave: row.dataset.clave, nombre: row.querySelector('.nm').value, icono: row.querySelector('.ic').value, digital: row.querySelector('.dg').checked ? 1 : 0, activo: row.querySelector('.ac').checked ? 1 : 0});
        } else if (t.classList.contains('mb')) {
            var r2 = t.closest('.medio-row');
            if (confirm('¿Borrar el medio de pago «' + r2.querySelector('.nm').value + '»?')) llamar('medio_borrar', {clave: r2.dataset.clave});
        }
    });
    document.addEventListener('input', function(e){ if (['marca_linea1','marca_linea2','empresa_nombre'].indexOf(e.target.id) >= 0) pintarMarca(); });
    var fileEl = document.getElementById('logo-file');
    if (fileEl) fileEl.addEventListener('change', function(){ if (fileEl.files[0]) llamar('subir_logo', {}, fileEl.files[0]).then(function(){ fileEl.value = ''; }); });

    fetch(API + '?accion=listar', {credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){ return r.json(); }).then(function(j){ if (j.respuesta) cargar(j); else toast(j.mensaje || 'Error', true); });

    if (location.hash === '#medios') { var tb = document.querySelector('.esc-tabs button[data-tab=medios]'); if (tb) tb.click(); }
    if (!EDITA) document.querySelectorAll('.esc-field input').forEach(function(i){ i.disabled = true; });
})();
</script>
</body>
</html>
