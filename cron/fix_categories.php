<?php
require('../includes/configure.php');
ini_set('../include_path', DIR_FS_CATALOG . PATH_SEPARATOR .
ini_get('../include_path'));
chdir(DIR_FS_CATALOG);
require_once('includes/application_top.php');

$noProducts = 0;
$updateResults = 0;
$catColumn = 'categories_id';

$sQ = selectQuery($noProducts, $db);
forLoop($sQ, $catColumn);
updateQuery($sQ, $db);


/**
 * query for viewing categories without products
 */
function selectQuery($noProducts, $db) {
   
    $sqlSelect = "SELECT * FROM ".TABLE_CATEGORIES." WHERE categories_id NOT IN 
    (SELECT categories.categories_id 
                    FROM ".TABLE_CATEGORIES." categories
                    RIGHT OUTER JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." products_to_categories
                    ON categories.categories_id = products_to_categories.categories_id
                    GROUP BY categories.categories_id) 
    AND categories_id NOT IN 
    (SELECT categories.parent_id 
                    FROM ".TABLE_CATEGORIES." 
                    RIGHT OUTER JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." products_to_categories
                    ON categories.categories_id = products_to_categories.categories_id
                    WHERE categories.parent_id <> 0
                    GROUP BY categories.parent_id)
    AND categories_id NOT IN
    (SELECT parent_id FROM ".TABLE_CATEGORIES." 
                    WHERE categories_id IN ( SELECT categories.parent_id 
                    FROM ".TABLE_CATEGORIES." categories
                    RIGHT OUTER JOIN  ".TABLE_PRODUCTS_TO_CATEGORIES." products_to_categories
                    ON categories.categories_id = products_to_categories.categories_id
                    WHERE categories.parent_id <> 0
                    GROUP BY categories.parent_id ) 
                    AND parent_id <> 0)
    AND categories_status <> 0
    GROUP BY categories_id";

    $noProducts = $db->Execute($sqlSelect);

    echo "----------Checking categories without products------------\n";
    echo 'Number of categories with no products: '. $noProducts->recordCount()."\n";

    return $noProducts;
}


/**
 * Update / Disable query
 */
function updateQuery($results, $db) {
    if ($results->recordCount() > 0) {
        echo "\n\n----------Updating/Disable category------------\n";

        $updateSelect = "UPDATE ".TABLE_CATEGORIES." SET categories_status = 0
        WHERE categories_id NOT IN 
        (SELECT categories.categories_id 
                        FROM ".TABLE_CATEGORIES." categories 
                        RIGHT OUTER JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." products_to_categories
                        ON categories.categories_id = products_to_categories.categories_id
                        GROUP BY categories.categories_id) 
        AND categories_id NOT IN 
        (SELECT categories.parent_id 
                        FROM ".TABLE_CATEGORIES." categories
                        RIGHT OUTER JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." products_to_categories
                        ON categories.categories_id = products_to_categories.categories_id
                        WHERE categories.parent_id <> 0
                        GROUP BY categories.parent_id)
        AND categories_id NOT IN
        (SELECT parent_id FROM ".TABLE_CATEGORIES." 
                    WHERE categories_id IN ( SELECT categories.parent_id 
                    FROM ".TABLE_CATEGORIES." categories
                    RIGHT OUTER JOIN  ".TABLE_PRODUCTS_TO_CATEGORIES." products_to_categories
                    ON categories.categories_id = products_to_categories.categories_id
                    WHERE categories.parent_id <> 0
                    GROUP BY categories.parent_id ) 
                    AND parent_id <> 0)
        AND categories_status <> 0";
    
        $updateResults = $db->Execute($updateSelect);

        echo 'Number of successfully disabled categories: '.$db->affectedRows();
    }
}



/**
 * for loop / viewing
 */
function forLoop($results, $catColumn) {
    if ($results->recordCount() > 0) {
        foreach ($results as $res) {
            echo "Category ID: ".$res[$catColumn]."\n";
        }
    }
}



