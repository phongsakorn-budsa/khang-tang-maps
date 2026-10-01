<?php 

class users{
    private $db;
    function __construct($con){
        $this->db =$con;
    }
    function insert_users($username,$password,$role,$first_name,$last_name,$phone_number,$email,$created_at){
        try{
            $result=$this->getUserByUserName($username);
            if($result["num"]>0){
                return false;
            }else{
                $new_password = md5($password.$username);
                $sql="INSERT INTO users(username,password,role,first_name,last_name,phone_number,email,created_at) VALUES(:username,:password,:role,:first_name,:last_name,:phone_number,:email,:created_at)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(":username",$username);
                $stmt->bindParam(":password",$new_password);
                $stmt->bindParam(":role",$role);
                $stmt->bindParam(":first_name",$first_name);
                $stmt->bindParam(":last_name",$last_name);
                $stmt->bindParam(":phone_number",$phone_number);
                $stmt->bindParam(":email",$email);
                $stmt->bindParam(":created_at",$created_at);
                $stmt->execute();
                return true;
            }
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }
    function getUserByUserName($username){
        try{
            $sql="SELECT COUNT(*) as num FROM users WHERE username=:username";
            $stmt=$this->db->prepare($sql);
            $stmt->bindParam(":username",$username);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result;
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }
    function getUser($username,$password){
        try{
            $sql="SELECT * FROM users WHERE username=:username AND password=:password";
            $stmt=$this->db->prepare($sql);
            $stmt->bindParam(":username",$username);
            $stmt->bindParam(":password",$password);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result;
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }

    function update_user($id, $first_name, $last_name, $phone, $email, $role){
        try{
            $sql = "UPDATE users SET
                        first_name   = :first_name,
                        last_name    = :last_name,
                        phone_number = :phone,
                        email        = :email,
                        role         = :role
                    WHERE user_id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":first_name", $first_name);
            $stmt->bindParam(":last_name", $last_name);
            $stmt->bindParam(":phone", $phone);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":role", $role);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            return $stmt->execute();

        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }
    function get_user_by_id($id){
    try{
        $sql = "SELECT user_id, username, first_name, last_name, phone_number, email, role
                FROM users
                WHERE user_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }catch(PDOException $e){
        echo $e->getMessage();
        return false;
    }
}

    function get_user_by_email($email){
        try{
            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }

    function update_password($user_id, $hashed_password){
        try{
            $sql = "UPDATE users SET password = :password WHERE user_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
            return $stmt->execute();
        }catch(PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }
}
?>