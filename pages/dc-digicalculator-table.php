<?php
function dc_digicalculator_table($options){
    global $product;
?>
    <form id="dc-form" class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
        <div class="row">
            <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Type product</label>
                <div class="col-sm-8 col-xs-12"> <select class="form-control input-sm" name="product_type">
                        <option value="default">Standaard</option>
                        <option value="brochures">Brochures</option>
                    </select> </div>
            </div>
            <hr>
            <?php foreach ($options as $option) { ?>
                <div class="dc-mainform <?php echo $option['id']; ?>" group="<?php echo $option['id']; ?>" style="<?php echo $option['id']=='default'?'display: block;':'display: none;';?>">
                    <div class="form-group product_size_both">
                        <label class="col-sm-4 col-xs-12 control-label">Planoformaat (B*H mm)</label>
                        <div class="col-sm-8 col-xs-12">
                            <select class="form-control input-sm" name="product_size">
                                <?php foreach ($option['options']['sizes'] as $size) {
                                    echo '<option value="' . $size['id'] . '">' . $size['name'] . '</option>';
                                } ?>
                                <option value="unique">Anders, nl</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group product_size_width" style="display: none;"> <label class="col-sm-4 col-xs-12 control-label">Planoformaat (B mm)</label>
                        <div class="col-sm-8 col-xs-12"> <input type="text" class="form-control input-sm" name="custom_width" value="210"> </div>
                    </div>
                    <div class="form-group product_size_height" style="display: none;"> <label class="col-sm-4 col-xs-12 control-label">Planoformaat (H mm)</label>
                        <div class="col-sm-8 col-xs-12"> <input type="text" class="form-control input-sm" name="custom_height" value="297"> </div>
                    </div>
                    <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Afloop</label>
                        <div class="col-sm-8 col-xs-12"> <input type="text" class="form-control input-sm" readonly="" value="2">
                            <input type="hidden" name="bleed" value="3">
                        </div>
                    </div>
                    <div class="form-group" style="<?php echo $option['id']=='default'?'display: none;':'display: block;';?>" > <label class="col-sm-4 col-xs-12 control-label">Aantal pagina's</label>
                        <div class="col-sm-8 col-xs-12"> <input type="number" class="form-control input-sm" name="pages" value="2" step="2"> </div>
                    </div>
                    <hr>
                    <?php if( $option['id']=='default' ){ ?>
                        <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Bedrukking</label>
                            <div class="col-sm-8 col-xs-12"> <select class="form-control input-sm" name="printtype">
                                    <option value="">Selecteer het gewenste bedrukking</option>
                                    <?php foreach ($option['options']['printtypes'] as $printtype) {
                                        echo '<option value="' . $printtype['id'] . '">' . $printtype['name'] . '</option>';
                                    } ?>
                                </select> 
                            </div>
                        </div>
                        <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Papiersoort</label>
                            <div class="col-sm-8 col-xs-12"> <select class="form-control input-sm" name="papertype">
                                    <option value="">Selecteer het gewenste papiersoort</option>
                                    <?php foreach ($option['options']['materials'] as $material) {
                                        foreach ($material['weights'] as &$weight) {
                                            $weight = $weight[0];
                                        }
                                        echo '<option weights="' . implode(',', $material['weights']) . '" value="' . $material['id'] . '">' . $material['name'] . '</option>';
                                    } ?>
                                </select> </div>
                        </div>
                        <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Gewicht</label>
                            <div class="col-sm-8 col-xs-12">
                                <select class="form-control input-sm" name="weight">
                                    <option value="">Selecteer het gewenste gewicht</option>
                                    <?php foreach ($option['options']['weights'] as $weight) {
                                        $weight = $weight['value'];
                                        echo '<option value="' . $weight . '">' . $weight . ' gr/m²</option>';
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="finishingtypes normal" style="display: block;">
                            <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Afwerking</label>
                                <div class="col-sm-8 col-xs-12">
                                    <select class="form-control input-sm" name="option[1]">
                                        <option value="">Selecteer het gewenste afwerkingstype</option>
                                        <?php foreach ($option['options']['generalfinish'] as $finish) {
                                            echo '<option value="' . $finish['id'] . '">' . $finish['name'] . '</option>';
                                        } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Luxe afwerking</label>
                                <div class="col-sm-8 col-xs-12"> <select class="form-control input-sm" name="option[4]">
                                        <option value="">Selecteer het gewenste afwerkingstype</option>
                                        <?php foreach ($option['options']['finish'] as $finish) {
                                            echo '<option value="' . $finish['id'] . '">' . $finish['name'] . '</option>';
                                        } ?>
                                    </select> </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Bedrukking omslag</label>
                            <div class="col-sm-8 col-xs-12"> <select class="form-control input-sm" name="printtype_cover">
                                    <option value="">Selecteer het gewenste bedrukking</option>
                                    <?php foreach ($option['options']['printtypes_cover'] as $printtype) {
                                        echo '<option value="' . $printtype['id'] . '">' . $printtype['name'] . '</option>';
                                    } ?>
                                </select> 
                            </div>
                        </div>
                        <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Papiersoort omslag</label>
                            <div class="col-sm-8 col-xs-12"> <select class="form-control input-sm" name="papertype_cover">
                                    <option value="">Selecteer het gewenste papiersoort</option>
                                    <?php foreach ($option['options']['materials_cover'] as $material) {
                                        foreach ($material['weights'] as &$weight) {
                                            $weight = $weight[0];
                                        }
                                        echo '<option weights="' . implode(',', $material['weights']) . '" value="' . $material['id'] . '">' . $material['name'] . '</option>';
                                    } ?>
                                </select> </div>
                        </div>
                        <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Gewicht omslag</label>
                            <div class="col-sm-8 col-xs-12">
                                <select class="form-control input-sm" name="weight_cover">
                                    <option value="">Selecteer het gewenste gewicht</option>
                                    <?php foreach ($option['options']['weights_cover'] as $weight) {
                                        $weight = $weight['value'];
                                        echo '<option value="' . $weight . '">' . $weight . ' gr/m²</option>';
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Bedrukking binnenwerk</label>
                            <div class="col-sm-8 col-xs-12"> <select class="form-control input-sm" name="printtype">
                                    <option value="">Selecteer het gewenste bedrukking</option>
                                    <?php foreach ($option['options']['printtypes_center'] as $printtype) {
                                        echo '<option value="' . $printtype['id'] . '">' . $printtype['name'] . '</option>';
                                    } ?>
                                </select> 
                            </div>
                        </div>
                        <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Papiersoort binnenwerk</label>
                            <div class="col-sm-8 col-xs-12"> <select class="form-control input-sm" name="papertype">
                                    <option value="">Selecteer het gewenste papiersoort</option>
                                    <?php foreach ($option['options']['materials_center'] as $material) {
                                        foreach ($material['weights'] as &$weight) {
                                            $weight = $weight[0];
                                        }
                                        echo '<option weights="' . implode(',', $material['weights']) . '" value="' . $material['id'] . '">' . $material['name'] . '</option>';
                                    } ?>
                                </select> </div>
                        </div>
                        <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Gewicht binnenwerk</label>
                            <div class="col-sm-8 col-xs-12">
                                <select class="form-control input-sm" name="weight">
                                    <option value="">Selecteer het gewenste gewicht</option>
                                    <?php foreach ($option['options']['weights_center'] as $weight) {
                                        $weight = $weight['value'];
                                        echo '<option value="' . $weight . '">' . $weight . ' gr/m²</option>';
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Afwerking</label>
                            <div class="col-sm-8 col-xs-12">
                                <select class="form-control input-sm" name="option[1]">
                                    <option value="">Selecteer het gewenste afwerkingstype</option>
                                    <?php foreach ($option['options']['brochdouble'] as $brochdouble) {
                                        echo '<option value="' . $brochdouble['id'] . '">' . $brochdouble['name'] .'</option>';
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Luxe afwerking omslag</label>
                            <div class="col-sm-8 col-xs-12">
                                <select class="form-control input-sm" name="option[2]">
                                    <option value="">Selecteer het gewenste afwerkingstype</option>
                                    <?php foreach ($option['options']['finish_cover'] as $finishCover) {
                                        echo '<option value="' . $finishCover['id'] . '">' . $finishCover['name'] .'</option>';
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Luxe afwerking binnenwerk</label>
                            <div class="col-sm-8 col-xs-12">
                                <select class="form-control input-sm" name="option[3]">
                                    <option value="">Selecteer het gewenste afwerkingstype</option>
                                    <?php foreach ($option['options']['finish_center'] as $finish) {
                                        echo '<option value="' . $finish['id'] . '">' . $finish['name'] .'</option>';
                                    } ?>
                                </select>
                            </div>
                        </div>
                    <?php } ?>
                    <hr>
                </div>
                <?php
            } ?>

            <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Aantal versies</label>
                <div class="col-sm-8 col-xs-12"> <input type="number" name="versions" class="form-control input-sm" value="1">
                </div>
            </div>
            <div class="form-group"> <label class="col-sm-4 col-xs-12 control-label">Eigen oplage</label>
                <div class="col-sm-8 col-xs-12"> <input type="number" name="dc-picked-quantity" class="form-control input-sm" value="1">
                </div>
            </div>
            <hr>   
        </div>
        <span class="button" id="dc-calculate">Berekenen</span>
        <div id="dc-prices"></div>

        <input type="hidden" name="quantity" value="1000">
        <button class="wp-element-button" type="submit" disabled name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" id="dc-order"><?php _e('Add to cart', 'woocommerce'); ?></button>


    </form>
<?php
}
