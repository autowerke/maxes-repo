<?php 
class dbo{
	function is_docker() {
        return file_exists('/.dockerenv');
    }

	function hostname() {
        if (self::is_docker()) {
            return "mariadb"; // Hostname container
        }
        return "localhost"; // Hostname Windows
    }

	function dbtype(){
		return "mysql";
	}
    
	function username(){
		return "root";
	}
    
	function password(){
		return "arilasso"; 
	}
    
	function dbname(){
		return "maxes_db"; 
	}
    
	function dbport(){
        if (self::is_docker()) {
            return "3306"; // Di dalam Docker, selalu 3306
        }
        // Jika di luar Docker, kita akses port external yang di-mapping
		return "3308"; 
	}
}

/*
Grant User Database:

docker exec -it maxes-mariadb-1 bash
Login:
mysql -u root -parilasso

GRANT ALL PRIVILEGES ON maxes_db.* TO 'maxes'@'%';
FLUSH PRIVILEGES;
QUIT;

docker-compose restart php


*/
?>