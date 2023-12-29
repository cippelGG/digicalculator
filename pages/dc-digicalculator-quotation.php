<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://use.typekit.net/wdp1lnm.css">
    <title>Offerte <?php echo $id; ?></title>
</head>
<style>
    * {
        margin: 0;
        padding: 0;
        font-family: nimbus-sans, sans-serif;
        font-size: 10pt;
    }
    h2 {
        font-size: 16pt;
    }
    body {
        width: 210mm;
        height: 297mm;
    }
    .address {
        position: absolute;
        top: 61mm;
        left: 26mm;
        width: 178mm;
    }
    .quotation {
        position: absolute;
        top: 92mm;
        left: 26mm;
        width: 168mm;
    }
    td {
        height: 5mm;
    }
    td:first-of-type {
        width: 53mm;
        font-weight: 700;
    }
</style>
<body>
    <!-- <?php echo json_encode($user); ?> -->
    <br/>
    <!-- <?php echo json_encode($quotedata); ?> -->
    <div class="header"></div>
    <div class="address">
        <?php echo $quotedata->quotation_obj->billing_addres->billing_company; ?><br/>
        T.a.v. <?php echo $quotedata->quotation_obj->billing_addres->billing_name; ?><br/>
        <?php echo $quotedata->quotation_obj->billing_addres->billing_address; ?><br/>
        <?php echo $quotedata->quotation_obj->billing_addres->billing_postCity; ?><br/>
        <?php echo $quotedata->quotation_obj->billing_addres->billing_country; ?><br/>
    </div>
    <div class="quotation">
        <h2>Offerte</h2>
        <table>
            <tr>
                <td>Offertenr.</td>
                <td>PC<?php echo $id; ?></td>
            </tr>
            <tr>
                <td>Datum</td>
                <td><?php echo implode('-',array_reverse(explode('-',explode(' ',$quotedata->date_created)[0]))); ?></td>
            </tr>
            <tr>
                <td>Betreft</td>
                <td><?php echo $quotedata->ext_ref; ?></td>
            </tr>
        </table>
        <br/>
        <p>Beste <?php echo $quotedata->quotation_obj->billing_addres->billing_first_name; ?>,</p>
        <br/>
        <p>In dank ontvingen wij uw verzoek tot het maken van een offerte. In antwoord hierop hebben wij het genoegen u geheel vrijblijvend aan te bieden:</p>
        <br/>
        <table>
            <?php
                foreach ($quotedata->quotation_obj->options as $value) {
                    ?><tr><td><?php echo $value->name;
                    ?></td><td colspan="2"><?php echo ($value->value);
                    ?></td></tr><?php
                }
            ?>
            <tr><td></td></tr>
            <?php
            $first = true;
                foreach ($quotedata->quotation_obj->prices as $key => $value) {
                    if( $first ){
                        $first = false;
                        ?><tr><td>Prijzen</td><?php
                    } else {
                        ?><tr><td></td><?php
                    }
                    ?><td><?php echo $key;
                    ?> ex.</td><td><?php echo wc_price(round($value[0],2));
                    ?></td></tr><?php
                }
            ?>            
        </table>
        <br>
        <p>Wij vertrouwen erop u met deze aanbieding van dienst te zijn. Als u vragen heeft over deze offerte dan horen wij het
graag. Wij hopen deze opdracht voor u te mogen uitvoeren en zien uw reactie graag tegemoet.</p>
        <br>
        <p>Deze prijzen zijn tot 4 weken na offertedatum geldig en zijn exclusief BTW.</p>
        <br>
        <p>Met vriendelijke groet,</p>
        <br>
        <p>De Groot Drukkerij bv</p>
    </div>
</body>
</html>