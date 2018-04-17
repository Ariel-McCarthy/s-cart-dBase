<?php
function getDatabaseConnection() {
    $host = "localhost";
    $user = "oculus";
    $pass = "Fuckmylife1";
    $db = "shopping_cart2"; 
    
$charset = 'utf8mb4';

//checking whether the URL contains "herokuapp" (using Heroku)
if(strpos($_SERVER['HTTP_HOST'], 'herokuapp') !== false) {
   $url = parse_url(getenv("CLEARDB_DATABASE_URL"));
   $host = $url["host"];
   $db   = substr($url["path"], 1);
   $user = $url["user"];
   $pass = $url["pass"];
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
PDO::ATTR_EMULATE_PREPARES => false,
];
$pdo = new PDO($dsn, $user, $pass, $opt);
return $pdo; 
    
}


function insertItemsIntoDB($items) {
    if (!$items) return; 
    
    $db = getDatabaseConnection(); 
    
    foreach ($items as $item) {
        $itemName = $item['name']; 
        $itemPrice = $item['salePrice']; 
        $itemImage = $item['thumbnailImage']; 
        
        $sql = "INSERT INTO item (item_id, name, price, image_url) VALUES (NULL, :itemName, :itemPrice, :itemURL)";
        $statement = $db->prepare($sql); 
        $statement->execute(array(
            itemName => $itemName, 
            itemPrice => $itemPrice, 
            itemURL => $itemImage
            ));
    }
}

function getMatchingItems($query, $category, $fromPrice, $toPrice, $order, $displayPics)
{
    //echo "$query <br/>";
    $db = getDatabaseConnection();
    //$sql = "SELECT * FROM item WHERE name LIKE '%$query%'";
    $imgSQL = "$showImages ? ', item.image_url': ";
    $sql = "SELECT DISTINCT item.item_id, item.name, item.price, item.image_url, 
           category.category_name FROM item INNER JOIN item_category 
           ON item.item_id = item_category.item_id INNER JOIN category 
           ON item_category.category_id =category.category_id  WHERE 1"; 
    
    if(!empty($query))
    {
        //This wasn't working because you needed a space before AND
        $sql.=" AND name LIKE '%$query%'";
    }
    
    //I'm gonna take the liberty to add all the other paramters here
    //WAIT! You have them alrady but.. 
    
    //These were down there. I moved them up.
    if (!empty($category)) 
    {
        $sql .= " AND category.category_name = '$category'";
    }
    if(!empty($priceFrom))
    {
        $sql.=" AND item.price >= '$priceFrom'";
    }
    if(!empty($ordering))
    {
        if(($ordering == 'product'))
        {
            $columnName = 'item.name';
        }
        else
        {
            $columnName = 'item.price';    
        }
        $sql.= " ORDER BY $columnName";
    }
    
    //See here- you're alrady executing your sql statement... but adding more to your sql afterwards?? 
    $statement = $db->prepare($sql);
    $statement->execute();
    $items = $statement->fetchAll();
    
    

    
    // if(isset($_GET['search-submitted']))
    // {
    //     //form was submitted
    // }
    
    return $items;
}

function getCategoriesHTML()
{
    $db = getDatabaseConnection(); 
    
    $categoriesHTML = "<option value=''></option>";
    
    //Where is your SQL statement?? You're missing it. Lemme add it.
    $sql = "SELECT category_name FROM category"; 

    $statement = $db->prepare($sql);
    $statement->execute();
    $records = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($records as $record)
    {
        $category = $record['category_name'];
        $categoriesHTML.= "<option value='$category'>$category</option>";
    }
    return $categoriesHTML;
}

function addCategoriesForItems($itemStart, $itemEnd, $category_id) {
    $db = getDatabaseConnection(); 
    
    for ($i = $itemStart; $i <= $itemEnd; $i++) {
        $sql = "INSERT INTO item_category (grouping_id, item_id, category_id) VALUES (NULL, '$i', '$category_id')";
        $db->exec($sql);
    }
        
}

//addCategoriesForItems(73, 82, 7); //Remove ASAP

/*function insertItemsIntoDB($items)
{   
    $db = getDatabaseConnection();
    foreach($items as $item)
    {
        $itemName = $item['name'];
        $itemPrice = $item['salePrice'];
        $itemImage = $item['thumbnailImage'];
        $itemId = $item['itemId'];
        
        $sql = "INSERT INTO item (item_id, name, price, img_url)
        VALUES (NULL, ':itemName', ':itemPrice', ':itemImage')"; 
        $statement = $db->prepare($sql);
        $statement->execute(array(
            itemName->$itemName,
            itemPrice=>$itemPrice,
            itemURL->$itemImage));
        
        $db->exec($sql);
        echo "$sql <br/>";
    }
    //inserts item into database

    //use exec() because no results are returned
    //$db->exec($sql);
}
*/
//insertItemsIntoDB();
?>