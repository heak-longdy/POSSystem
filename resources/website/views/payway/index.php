<!DOCTYPE html>
<html lang="en">

<head>
    <title>PayWay Checkout</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="author" content="PayWay">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
</head>

<body>
<div id="aba_main_modal" class="aba-modal">
    <div class="aba-modal-content">
        <form method="POST"  action="<?php echo $ABA_PAYWAY_API_URL ?>" id="aba_merchant_request">
            <?php
                foreach($params as $key => $each_param){
                    echo '<input type="hidden" name="'.$key.'" value="'.$each_param.'">';
                }
            ?>
        </form>
    </div>
</div>
<!-- <script src="https://checkout-uat.payway.com.kh/plugins/checkout2-0-dev.js"></script> -->
<script src="https://checkout.payway.com.kh/plugins/checkout2-0-dev.js"></script>
<!--<link rel="stylesheet" href="https://payway.ababank.com/checkout-popup.html?file=css"/>
<script src="https://payway.ababank.com/checkout-popup.html?file=js"></script> -->
<script>
    $(document).ready(function(){
        document.getElementById("aba_merchant_request").submit();
    });
</script>
</body>
</html>