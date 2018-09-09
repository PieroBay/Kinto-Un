<?php

    function compo($titre,$de,$a,$isCompare=false,$isPrice=false){
        if((($de != "-" && $a != "-") && ($de != "" && $a != "") && ($de != 0 && $a != 0)) || $isCompare){
        $price = ($isPrice)?' €':'';
            echo '<div class="oo-1 dea">
                <div class="oo-35 oo-s-1">'.$titre.' :</div>
                <div class="oo-45 oo-s-1"><span>De</span> '.$de.$price.' <span>à</span> '.$a.$price.'</div>
            </div>';
        }
    }