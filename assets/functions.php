<?php


/**
 * Permet de faire une requête PDO à la base de données (query ou prepare)
 *
 * @param PDO $pdo
 * @param string $sql
 * @param array $params
 * @return PDOStatement
 */
function dbQuery(PDO $pdo, string $sql, array $params = []): PDOStatement
{
    if(empty($params)){
        return $pdo->query($sql);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Permet de récupèrer un tableau de données venant de la bdd
 *
 * @param PDO $pdo
 * @param string $sql
 * @param array $params
 * @return array
 */
function fetchAll(PDO $pdo, string $sql, array $params = []): array
{
    return dbQuery($pdo, $sql, $params)->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Permet de récupèrer une seule information venant de la bdd
 *
 * @param PDO $pdo
 * @param string $sql
 * @param array $params
 * @return array|null
 */
function fetchOne(PDO $pdo, string $sql, array $params = []): ?array
{
    $stmt = dbQuery($pdo, $sql, $params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt->closeCursor();

    return $result ?: null;
}


/**
 * Permet de donner sur une requête SELECT le nombre d'entrée
 *
 * @param PDO $pdo
 * @param string $sql
 * @param array $params
 * @return integer|null
 */
function myCount(PDO $pdo, string $sql, array $params = []): ?int
{
      return dbQuery($pdo, $sql, $params)->rowCount();
}

/**
 * Permet d'insèrer un élément à la base de données
 *
 * @param PDO $pdo
 * @param string $sql
 * @param array $params
 * @return string
 */
function insert(PDO $pdo, string $sql, array $params = []): string
{
    dbQuery($pdo, $sql, $params);
    return $pdo->lastInsertId();
}

/**
 * Permet de modifier ou supprimer dans la base de données
 *
 * @param PDO $pdo
 * @param string $sql
 * @param array $params
 * @return integer
 */
function execute(PDO $pdo, string $sql, array $params = []): int
{
    return dbQuery($pdo, $sql, $params)->rowCount();
}

/**
 * Permet de créer un système de pagination
 *
 * @param PDO $pdo
 * @param string $sql
 * @param integer $offset
 * @param integer $limit
 * @param array $params
 * @return array
 */
function pagination(PDO $pdo, string $sql, int $offset, int $limit, array $params = []): array
{
    // pagination SQL 
    // SELECT * FROM products LIMIT 10
    // SELECT * FROM products LIMIT 0,10
    // SELECT * FROM table LIMIT offset,limit
    // limit c'est combien j'en prends
    // offset c'est à partir d'où
    // ex SELECT * FROM products LIMIT 10,10
    // ex SELECT * FROM products LIMIT 20,10
    // ex SELECT * FROM products LIMIT 30,10

    $stmt = $pdo->prepare($sql);
    // insérer les paramètre de la requête
    // ex SELECT * FROM products WHERE id_category=:cat LIMIT :offset,:limit
    // [value, value, value]
    /*
        [
            "key" => "value",
            "key" => "value
        ]
    */
    // Attention au & devant $value : obligatoire avec un bindParam qui boucle
    // le & devant $value crée un pointeur/référence dont bindParam à besoin lorsqu'il est bouclé
    foreach($params as $key => &$value){
        // (condition) ? return si true : return si false
        /*
            if(condition)
            {
                return si True
            }else{
                return si false
            }
        
        */ 
        $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        // si tableau en indice bindParam veut que le param commence à 1 et pas à 0 (1er d'un tableau)
        $paramKey = is_int($key) ? $key + 1 : $key;
        $stmt->bindParam($paramKey,$value,$paramType);
    }


     // insèrer les paramètres pour la pagination
    $stmt->bindParam(":offset",$offset,PDO::PARAM_INT);
    $stmt->bindParam(":limit",$limit,PDO::PARAM_INT);


    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}