/**
 * Gets a customized layout for additional information for a selected importer
 * @return
 */
function tiendaGetImporterLayout( element, container )
{
    var url = 'index.php?format=raw&option=com_tienda&task=doTask&element=plgTiendaTool_GenericImporter&'+
    		  'elementTask=getHtmlStepAjax&importer='+element.value;

    tiendaDoTask( url, container, document.adminForm );
}
