<?php

class Filters extends model{

    public function getFilters($filters){

        $products =new Products();
        $brands = new Brands();
        $array = array(
            'brands' => array(),
            'maxslider' => 1000,
            'stars' => array(
                '0' => 0,
                '1' => 0,
                '2' => 0,
                '3' => 0,
                '4' => 0,
                '5' => 0
            ),
            'sale' => 0,
            'options' => array()
        );

        $array['brands'] = $brands->getList();
        $brand_products = $products->getListOfBrands($filters);
        //criando filtro de marcas
        foreach ($array['brands'] as $bkey => $bitem){
            $array['brands'][$bkey]['count'] = 0;
            foreach ($brand_products as $bproduct){
                if($bproduct['id_brand'] == $bitem['id']){
                    $array['brands'][$bkey]['count'] = $bproduct['c'];
                }
            }
            if($array['brands'][$bkey]['count'] == 0){
                unset($array['brands'][$bkey]);
            }
        }
        //Criando filtro de preco
        $array['maxslider'] = $products->getMaxPrice($filters);

        //criando filtro das estrelas
        $star_product = $products->getListOfStars($filters);
        foreach ($array['stars'] as $skey => $sitem){
            foreach ($star_product as $sproduct){
                if($sproduct['rating'] == $skey){
                    $array['stars'][$skey] = $sproduct['c'];
                }
            }
        }

        //criando filtro das promocoes
        $array['sale'] = $products->getSaleCount($filters);

        // criando filtro das opcoes
        $array['options'] = $products->getAvailableOptions($filters);

        return $array;
    }
}