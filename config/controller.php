<?php 

class controller{
    private $db;

    function __construct($con){
        $this->db =$con;
    }

    function insert_shop($owner_id,$shop_name,$description,$shop_image,$latitude,$longitude,$is_open){
        try{
            $sql="INSERT INTO shops(owner_id,shop_name,description,shop_image,latitude,longitude,is_open)
            VALUES(:owner_id,:shop_name,:description,:shop_image,:latitude,:longitude,:is_open)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":owner_id",$owner_id);
            $stmt->bindParam(":shop_name",$shop_name);
            $stmt->bindParam(":description",$description);
            $stmt->bindParam(":shop_image",$shop_image);
            $stmt->bindParam(":latitude",$latitude);
            $stmt->bindParam(":longitude",$longitude);
            $stmt->bindParam(":is_open",$is_open);
            $stmt->execute();

            return true;
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }
    function insert_products($shop_id,$product_name,$product_detail,$product_type,$price,$product_image,$status){
        try{
            $sql="INSERT INTO products(shop_id,product_name,product_detail,product_type,price,product_image,status)
            VALUES(:shop_id,:product_name,:product_detail,:product_type,:price,:product_image,:status)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":shop_id",$shop_id);
            $stmt->bindParam(":product_name",$product_name);
            $stmt->bindParam(":product_detail",$product_detail);
            $stmt->bindParam(":product_type",$product_type);
            $stmt->bindParam(":price",$price);
            $stmt->bindParam(":product_image",$product_image);
            $stmt->bindParam(":status",$status);
            $stmt->execute();

            return true;
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }
    function insert_report_shop($shop_id,$report_type,$detail,$report_date,$admin_status){
        try{
            $sql="INSERT INTO reports(shop_id,report_type,detail,report_date,admin_status)
            VALUES(:shop_id,:report_type,:detail,:report_date,:admin_status)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":shop_id",$shop_id);
            $stmt->bindParam(":report_type",$report_type);
            $stmt->bindParam(":detail",$detail);
            $stmt->bindParam(":report_date",$report_date);
            $stmt->bindParam(":admin_status",$admin_status);
            $stmt->execute();

            return true;
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }

    // 1. ฟังก์ชันดึงข้อมูลร้านค้า ของ User ที่ Login อยู่
    function get_my_shop($owner_id){
        try{
            $sql = "SELECT * FROM shops WHERE owner_id = :owner_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":owner_id", $owner_id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC); // คืนค่าข้อมูลร้าน หรือ false ถ้าไม่มี
        }catch(PDOException $e){
            return false;
        }
    }

    // 2. ฟังก์ชันดึงสินค้าทั้งหมดของร้าน (เฉพาะที่ยังไม่ลบ)
    public function get_my_products($shop_id){
    try{
        $sql = "SELECT * 
                FROM products 
                WHERE shop_id = :shop_id 
                  AND is_deleted = 0
                ORDER BY product_id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":shop_id", (int)$shop_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }catch(PDOException $e){
        return [];
    }
}


    function get_product($id){
        try{
            $sql="SELECT * FROM products
            WHERE product_id=:id";
            $stmt=$this->db->prepare($sql);
            $stmt->bindParam(":id",$id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }
    function get_shop($id){
        try{
            $sql="SELECT * FROM shops
            WHERE shop_id=:id";
            $stmt=$this->db->prepare($sql);
            $stmt->bindParam(":id",$id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }

    public function get_all_open_shops(){
    try{
        $sql = "SELECT 
                    shop_id,
                    shop_name,
                    description,
                    latitude,
                    longitude,
                    shop_image
                FROM shops
                WHERE is_open = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }catch(PDOException $e){
        return [];
    }
}
public function get_all_open_shops_with_products(){
    try{
        $sql = "
            SELECT 
                s.shop_id, s.shop_name, s.description, s.shop_image, s.latitude, s.longitude, s.shop_type,
                GROUP_CONCAT(DISTINCT p.product_name SEPARATOR '||') AS product_names
            FROM shops s
            LEFT JOIN products p 
                ON p.shop_id = s.shop_id
                AND (p.is_deleted = 0 OR p.is_deleted IS NULL)
                AND (p.status = 'available' OR p.status IS NULL)
            WHERE s.is_open = 1
              AND (s.is_deleted = 0 OR s.is_deleted IS NULL)
            GROUP BY s.shop_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // แปลง product_names เป็น array
        foreach($rows as &$r){
            $r['products'] = !empty($r['product_names'])
                ? explode('||', $r['product_names'])
                : [];
            unset($r['product_names']);
        }
        return $rows;

    }catch(PDOException $e){
        return [];
    }
}


public function get_dashboard_stats(){
    try{
        $data = [];

        // ผู้ใช้ทั้งหมด
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users");
        $stmt->execute();
        $data['users'] = $stmt->fetchColumn();

        // ร้านค้าทั้งหมด
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM shops WHERE is_deleted = 0 OR is_deleted IS NULL");
        $stmt->execute();
        $data['shops'] = $stmt->fetchColumn();

        // สินค้าทั้งหมด
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE is_deleted = 0 OR is_deleted IS NULL");
        $stmt->execute();
        $data['products'] = $stmt->fetchColumn();

        //รายงาน
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reports");
        $stmt->execute();
        $data['reports'] = $stmt->fetchColumn();

        return $data;

    }catch(PDOException $e){
        return false;
    }
}

function get_all_users(){
    try{
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }catch(PDOException $e){
        echo $e->getMessage();
        return false;
    }
}


function get_all_shops(){
    try{
        $stmt = $this->db->prepare("SELECT shop_id, shop_name FROM shops");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }catch(PDOException $e){
        echo $e->getMessage();
        return false;
    }
}

public function get_shop_with_owner($shop_id){
    try{
        $sql = "
            SELECT s.*,
                   u.first_name,
                   u.last_name,
                   u.phone_number,
                   u.username
            FROM shops s
            JOIN users u ON s.owner_id = u.user_id
            WHERE s.shop_id = :shop_id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':shop_id', (int)$shop_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }catch(PDOException $e){
        echo $e->getMessage();
        return false;
    }
}

public function search_products($keyword = '', $shop_id = ''){
    try{
        $sql = "SELECT p.*, s.shop_name
                FROM products p
                JOIN shops s ON p.shop_id = s.shop_id
                WHERE p.is_deleted = 0";

        $params = [];

        if($keyword !== ''){
            $sql .= " AND p.product_name LIKE :kw";
            $params[':kw'] = "%{$keyword}%";
        }
        if($shop_id !== ''){
            $sql .= " AND p.shop_id = :shop_id";
            $params[':shop_id'] = (int)$shop_id;
        }

        $sql .= " ORDER BY p.product_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }catch(PDOException $e){
        return [];
    }
}


public function search_shops($keyword = '', $status = ''){
    try{
        $sql = "SELECT s.*, u.first_name, u.last_name
                FROM shops s
                JOIN users u ON s.owner_id = u.user_id
                WHERE s.is_deleted = 0";
        $params = [];

        if($keyword !== ''){
            $sql .= " AND s.shop_name LIKE :kw";
            $params[':kw'] = "%{$keyword}%";
        }

        if($status !== ''){
            $sql .= " AND s.is_open = :st";
            $params[':st'] = (int)$status;
        }

        $sql .= " ORDER BY s.shop_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }catch(PDOException $e){
        return [];
    }
}



function search_users($keyword='', $role=''){
    try{
        $sql = "SELECT * FROM users WHERE 1";
        $params = [];

        if($keyword != ''){
            $sql .= " AND (
                username LIKE :kw
                OR first_name LIKE :kw
                OR last_name LIKE :kw
            )";
            $params[':kw'] = "%$keyword%";
        }

        if($role != ''){
            $sql .= " AND role = :role";
            $params[':role'] = $role;
        }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }catch(PDOException $e){
        echo $e->getMessage();
        return false;
    }
}

    function update_product($product_id, $product_name, $product_detail,$product_type,$price, $product_image,$status){
        try{
            $sql = "UPDATE products SET
                    product_name = :product_name,
                    product_detail=:product_detail,
                    product_type=:product_type,
                    price = :price,
                    product_image = :product_image,
                    status = :status
                    WHERE product_id = :product_id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(":product_name", $product_name);
                    $stmt->bindParam(":product_detail", $product_detail);
                    $stmt->bindParam(":product_type", $product_type);
                    $stmt->bindParam(":price", $price);
                    $stmt->bindParam(":product_image", $product_image);
                    $stmt->bindParam(":status", $status);
                    $stmt->bindParam(":product_id", $product_id);
                    $stmt->execute();
                    return true;
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }
    function update_shop($shop_id, $owner_id, $shop_name, $description, $shop_image,$latitude,$longitude, $shop_type = null){
        try{
            $sql = "UPDATE shops SET 
                    owner_id = :owner_id,
                    shop_name = :shop_name,
                    description = :description,
                    shop_image = :shop_image,
                    latitude = :latitude,
                    longitude = :longitude,
                    shop_type = :shop_type
                    WHERE shop_id = :shop_id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(":shop_id", $shop_id);
                    $stmt->bindParam(":owner_id", $owner_id);
                    $stmt->bindParam(":shop_name", $shop_name);
                    $stmt->bindParam(":description", $description);
                    $stmt->bindParam(":shop_image", $shop_image);
                    $stmt->bindParam(":latitude", $latitude);
                    $stmt->bindParam(":longitude", $longitude);
                    $stmt->bindParam(":shop_type", $shop_type);
                    $stmt->execute();
                    return true;
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }
    function update_shop_admin($shop_id, $owner_id, $shop_name, $description, $shop_image,$latitude,$longitude,$is_open, $shop_type){
        try{
            $sql = "UPDATE shops SET 
                    owner_id = :owner_id,
                    shop_name = :shop_name,
                    description = :description,
                    shop_image = :shop_image,
                    latitude = :latitude,
                    longitude = :longitude,
                    is_open = :is_open,
                    shop_type = :shop_type
                    WHERE shop_id = :shop_id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(":shop_id", $shop_id);
                    $stmt->bindParam(":owner_id", $owner_id);
                    $stmt->bindParam(":shop_name", $shop_name);
                    $stmt->bindParam(":description", $description);
                    $stmt->bindParam(":shop_image", $shop_image);
                    $stmt->bindParam(":latitude", $latitude);
                    $stmt->bindParam(":longitude", $longitude);
                    $stmt->bindParam(":is_open", $is_open);
                    $stmt->bindParam(":shop_type", $shop_type);
                    $stmt->execute();
                    return true;
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }
    


    public function delete_product($id){
    try{
        $sql = "UPDATE products 
                SET is_deleted = 1 
                WHERE product_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }catch(PDOException $e){
        echo $e->getMessage();
        return false;
    }
}

public function delete_shop($shop_id){
    try{
        $this->db->beginTransaction();

        // 1) soft delete ร้าน
        $sql1 = "UPDATE shops SET is_deleted = 1 WHERE shop_id = :id";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->bindValue(":id", (int)$shop_id, PDO::PARAM_INT);
        $stmt1->execute();

        // 2) soft delete สินค้าทั้งร้าน
        $sql2 = "UPDATE products SET is_deleted = 1 WHERE shop_id = :id";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->bindValue(":id", (int)$shop_id, PDO::PARAM_INT);
        $stmt2->execute();

        $this->db->commit();
        return true;

    }catch(PDOException $e){
        $this->db->rollBack();
        return false;
    }
}



    function delete_user($id){
        try{
            $sql ="DELETE FROM users WHERE user_id =:id";
            $stmt = $this->db->prepare($sql);
            $stmt ->bindParam(":id",$id);
            $stmt->execute();

            return true;
        }catch(PDOException $e){
            echo $e-> getMessage();
            return false;
        }
    }

    public function update_shop_status($shop_id, $status)
{
    try {
        $sql = "UPDATE shops
                SET is_open = :status
                WHERE shop_id = :shop_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":status", $status, PDO::PARAM_INT);
        $stmt->bindParam(":shop_id", $shop_id, PDO::PARAM_INT);

        return $stmt->execute();

    } catch (PDOException $e) {
        // ใช้ตอน debug
        // echo $e->getMessage();
        return false;
    }
}


    public function update_product_status($product_id, $status)
{
    try{
        $sql = "UPDATE products
                SET status = :status
                WHERE product_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $product_id, PDO::PARAM_INT);

        return $stmt->execute();
    }catch(PDOException $e){
        return false;
    }
}

public function get_reports($keyword = '', $status = '')
{
    try{
        $sql = "SELECT r.*, s.shop_name
                FROM reports r
                JOIN shops s ON r.shop_id = s.shop_id
                WHERE 1";

        $params = [];

        if($keyword !== ''){
            $sql .= " AND (s.shop_name LIKE :kw OR r.report_type LIKE :kw OR r.detail LIKE :kw)";
            $params[':kw'] = "%{$keyword}%";
        }

        if($status !== ''){
            $sql .= " AND r.admin_status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY r.report_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }catch(PDOException $e){
        // echo $e->getMessage();
        return [];
    }
}

public function update_report_status($report_id, $status)
{
    try{
        $sql = "UPDATE reports SET admin_status = :status WHERE report_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', (int)$report_id, PDO::PARAM_INT);
        return $stmt->execute();
    }catch(PDOException $e){
        return false;
    }
}

public function delete_report($report_id)
{
    try{
        $sql = "DELETE FROM reports WHERE report_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$report_id, PDO::PARAM_INT);
        return $stmt->execute();
    }catch(PDOException $e){
        return false;
    }
}

public function get_reports_for_owner($owner_id, $status = '')
{
    try{
        $sql = "SELECT r.*, s.shop_name, s.shop_id
                FROM reports r
                JOIN shops s ON r.shop_id = s.shop_id
                WHERE s.owner_id = :owner_id";

        $params = [':owner_id' => (int)$owner_id];

        if($status !== ''){
            $sql .= " AND r.seller_status = :st";
            $params[':st'] = $status;
        }

        $sql .= " ORDER BY r.report_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }catch(PDOException $e){
        return [];
    }
}

public function update_report_seller_status($report_id, $seller_status, $seller_note = null)
{
    try{
        $sql = "UPDATE reports
                SET seller_status = :seller_status,
                    seller_note = :seller_note
                WHERE report_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':seller_status', $seller_status);
        $stmt->bindValue(':seller_note', $seller_note);
        $stmt->bindValue(':id', (int)$report_id, PDO::PARAM_INT);

        return $stmt->execute();

    }catch(PDOException $e){
        return false;
    }
}


public function get_reports_v2($keyword = '', $admin_status = '', $seller_status = '')
{
    try{
        $sql = "SELECT r.*, s.shop_name
                FROM reports r
                JOIN shops s ON r.shop_id = s.shop_id
                WHERE 1";
        $params = [];

        if($keyword !== ''){
            $sql .= " AND (s.shop_name LIKE :kw OR r.report_type LIKE :kw OR r.detail LIKE :kw OR r.seller_note LIKE :kw)";
            $params[':kw'] = "%{$keyword}%";
        }

        if($admin_status !== ''){
            $sql .= " AND r.admin_status = :admin_status";
            $params[':admin_status'] = $admin_status;
        }

        if($seller_status !== ''){
            $sql .= " AND r.seller_status = :seller_status";
            $params[':seller_status'] = $seller_status;
        }

        $sql .= " ORDER BY r.report_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }catch(PDOException $e){
        return [];
    }
}

public function update_report_admin_status($report_id, $admin_status)
{
    try{
        $sql = "UPDATE reports SET admin_status = :admin_status WHERE report_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':admin_status', $admin_status);
        $stmt->bindValue(':id', (int)$report_id, PDO::PARAM_INT);
        return $stmt->execute();
    }catch(PDOException $e){
        return false;
    }
}
public function update_user_profile($user_id, $first_name, $last_name, $phone_number, $email){
    try{
        $sql = "UPDATE users
                SET first_name = :fn, last_name = :ln, phone_number = :ph, email = :em
                WHERE user_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":fn", $first_name);
        $stmt->bindValue(":ln", $last_name);
        $stmt->bindValue(":ph", $phone_number);
        $stmt->bindValue(":em", $email);
        $stmt->bindValue(":id", (int)$user_id, PDO::PARAM_INT);
        return $stmt->execute();
    }catch(PDOException $e){
        return false;
    }
}










}
?>