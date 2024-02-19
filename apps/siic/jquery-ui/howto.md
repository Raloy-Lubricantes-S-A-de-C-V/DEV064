# jQuery - UI

## Uso de  Dialogos ( dialog )

### Cabeceras en archivo HTML

    <link rel="stylesheet" type="text/css" href="../jquery-ui/jquery-ui.min.css">
    <link rel="stylesheet" type="text/css" href="../jquery-ui/jquery-ui.theme.min.css">
    <script type="text/javascript" src="../jquery-ui/jquery-ui.min.js"></script>

### Declaración del elemento Dialogo en archivo HTML
    <div id="dialogo" title="Detalle de Requisición">
        <p></p>
    </div>

### Constructor del Dialogo en archivo JS

    $('#dialogo').dialog({
        autoOpen: false,
        width: 650,
        buttons: [
            {
                text: "Cerrar",
                click: function() {
                    $( this ).dialog( "close" );
                }
            }
        ]
    });

### Mostrando el Dialogo en archivo JS

    $('.ui-dialog-title').html("Detalle de la Requisición " + request.r); // Modificar el Titulo del Dialogo
    $('#dialogo').find('p').html(html); // Insertar contenido del Dialogo
    $('#dialogo').dialog('open'); // Mostrar el Dialogo

