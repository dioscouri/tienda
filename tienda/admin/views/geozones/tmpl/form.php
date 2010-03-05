<?php
defined('_JEXEC') or die('Restricted access');
$form = @$this->form;
$row = @$this->row;
JFilterOutput::objectHTMLSafe( $row );
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >

    <fieldset>
        <legend><?php echo JText::_('Form'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <label for="geozone_name">
                        <?php echo JText::_( 'Name' ); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="geozone_name" id="geozone_name" size="48" maxlength="250" value="<?php echo @$row->geozone_name; ?>" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <label for="geozone_description">
                        <?php echo JText::_( 'Description' ); ?>:
                        </label>
                    </td>
                    <td>
                        <textarea name="geozone_description" id="geozone_description" rows="5" cols="25"><?php echo @$row->geozone_description; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <label for="geozone_type">
                        <?php echo JText::_( 'Type' ); ?>:
                        </label>
                    </td>
                    <td>
                        <?php echo TiendaSelect::geozonetypes( @$row->geozonetype_id, 'geozonetype_id', '', 'geozonetype_id', true ); ?>
                    </td>
                </tr>
            </table>
            <input type="hidden" id="geozone_id" name="id" value="<?php echo @$row->geozone_id; ?>" />
            <input type="hidden" name="task" value="" />
    </fieldset>
</form>