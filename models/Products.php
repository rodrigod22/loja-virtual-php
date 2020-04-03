<?php
class Products extends model {

    public function getAvailableOptions($filters = array()){
        $groups = array();
        $ids = array();

        $where = $this->buildWhere($filters);

        $sql = "SELECT 
        id, options 
        FROM products
        WHERE ".implode(' AND ', $where);
        $sql = $this->db->prepare($sql);
        $this->bindWhere($filters, $sql);
        $sql->execute();

        if($sql->rowCount() > 0){
            foreach ($sql->fetchAll() as $product){
                $ops = explode(",", $product['options']);
                $ids[] = $product['id'];
                foreach ($ops as $op){
                    if(!in_array($op, $groups) && !empty($op)){
                        $groups[] = $op;
                    }
                }
            }
        }
        $options = $this->getAvailableValuesFromOptions($groups, $ids);

        return $options;
    }

    public function getAvailableValuesFromOptions($groups, $ids) {
        $array = array();
        $options = new Options();
        foreach($groups as $op) {
            $array[$op] = array(
                'name' => $options->getName($op),
                'options' => array()
            );
        }
        $sql = "SELECT
		p_value,
		id_option,
		COUNT(id_option) as c
		FROM products_options
		WHERE
		id_option IN ('".implode("','", $groups)."') AND
		id_product IN ('".implode("','", $ids)."')
		GROUP BY p_value ORDER BY id_option";

        $sql = $this->db->query($sql);
        if($sql->rowCount() > 0) {
            foreach($sql->fetchAll() as $ops) {
                $array[$ops['id_option']]['options'][] = array(
                    'id' => $ops['id_option'],
                    'value'=>$ops['p_value'],
                    'count'=>$ops['c']
                );
            }
        }
        return $array;
    }

    public function getSaleCount($filters = array()){
        $where = $this->buildWhere($filters);
        $where[] = 'sale = "1"';

        $sql = "SELECT 
        COUNT(*) as c       
        FROM products 
        WHERE ".implode(' AND ', $where);

        $sql = $this->db->prepare($sql);
        $this->bindWhere($filters, $sql);
        $sql->execute();
        if($sql->rowCount() > 0 ){
            $sql = $sql->fetch();
            return $sql['c'];
        }else{
            return '0';
        }
    }


    public function getMaxPrice($filters = array()){

        $where = $this->buildWhere($filters);
        $sql = "SELECT 
        price         
        FROM products 
        WHERE ".implode(' AND ', $where)."
        ORDER BY price DESC
        LIMIT 1";
        $sql = $this->db->prepare($sql);
        $this->bindWhere($filters, $sql);

        $sql->execute();
        if($sql->rowCount() > 0 ){
            $sql = $sql->fetch();
            return $sql['price'];
        }else{
            return '0';
        }

    }

    public function getListOfStars($filters = array()){
        $array = array();
        $where = $this->buildWhere($filters);

        $sql = "SELECT 
        rating, 
        COUNT(id) as c 
        FROM products 
        WHERE ".implode(' AND ', $where)."
        GROUP BY rating";
        $sql = $this->db->prepare($sql);
        $this->bindWhere($filters, $sql);
        $sql->execute();
        if($sql->rowCount() > 0 ){
            $array = $sql->fetchAll();
        }
        return $array;
    }

    public function getListOfBrands($filters = array()){
        $array = array();

        $where = $this->buildWhere($filters);

        $sql = "SELECT 
        id_brand, 
        COUNT(id) as c 
        FROM products 
        WHERE ".implode(' AND ', $where)."
        GROUP BY id_brand";
        $sql = $this->db->prepare($sql);
        $this->bindWhere($filters, $sql);
        $sql->execute();
        if($sql->rowCount() > 0 ){
            $array = $sql->fetchAll();
        }
        return $array;
    }

	public function getList($offset = 0, $limit = 3, $filters = array()) {
		$array = array();

        $where = $this->buildWhere($filters);

		$sql = "SELECT 
        *,
		( select brands.name from brands where brands.id = products.id_brand ) as brand_name,
		( select categories.name from categories where categories.id = products.id_category ) as category_name
		FROM 
		products 
		WHERE ".implode(' AND ', $where)."
		LIMIT $offset, $limit";
		$sql = $this->db->prepare($sql);
        $this->bindWhere($filters, $sql);
        $sql->execute();
		if($sql->rowCount() > 0) {
			$array = $sql->fetchAll();
			foreach($array as $key => $item) {
				$array[$key]['images'] = $this->getImagesByProductId($item['id']);
			}
		}
		return $array;
	}

	public function getImagesByProductId($id) {
		$array = array();

		$sql = "SELECT url FROM products_images WHERE id_product = :id";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(":id", $id);
		$sql->execute();

		if($sql->rowCount() > 0) {
			$array = $sql->fetchAll();
		}

		return $array;
	}

	public function getTotal($filters = array()){

        $where = $this->buildWhere($filters);

	    $sql = "SELECT 
        COUNT(*) as c 
        FROM products
        WHERE ".implode(' AND ', $where);
        $sql = $this->db->prepare($sql);
        $this->bindWhere($filters, $sql);
        $sql->execute();
        $sql =$sql->fetch();
        return $sql['c'];
	}

	private function buildWhere($filters){
        $where = array(
            '1=1'
        );
        if(!empty($filters['category'])){
            $where[] = "id_category = :id_category";
        }
        return $where;
    }
    private function bindWhere($filters, &$sql){
        if(!empty($filters['category'])){
            $sql->bindValue(":id_category", $filters['category']);
        }
    }

}