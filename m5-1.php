<?php
$dsn = 'mysql:dbname=tb250436db;host=localhost';
$user = 'tb-250436';
$password = 'CCMAmgcKM6';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

// テーブル作成 SQL
$sql = "CREATE TABLE IF NOT EXISTS tbast (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name CHAR(255),
    comment TEXT,
    post_time DATETIME,
    password CHAR(255)
);";
$stmt = $pdo->query($sql);

$name_variable = "";
$comment_variable = "";
$edit_id_variable = "";
$edit_password_variable = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["delete_number"]) && isset($_POST["delete_password"]) && !empty($_POST["delete"])) {
        $deleteNumber = (int)$_POST["delete_number"];
        $delete_password = (string)$_POST["delete_password"];

        // パスワードが空でないか確認
        if ($delete_password === "") {
            echo "";
        } else {
            // 削除時のパスワード確認
            $sql_check_password = "SELECT password FROM tbast WHERE id = :deleteNumber";
            $stmt_check_password = $pdo->prepare($sql_check_password);
            $stmt_check_password->bindParam(':deleteNumber', $deleteNumber, PDO::PARAM_INT);
            $stmt_check_password->execute();
            $result = $stmt_check_password->fetch();

            if (empty($result)) {
                echo "";
            } elseif ($result['password'] === $delete_password) {
                // パスワードが一致した場合のみ削除
                $sql_delete = "DELETE FROM tbast WHERE id = :deleteNumber";
                $stmt_delete = $pdo->prepare($sql_delete);
                $stmt_delete->bindParam(':deleteNumber', $deleteNumber, PDO::PARAM_INT);
                $stmt_delete->execute();
            } else {
                echo "";
            }
        }
    } elseif (!empty($_POST["name"]) && !empty($_POST["comment"])) {
        $name = $_POST["name"];
        $comment = $_POST["comment"];
        
        // パスワード (password) を受け取ります。パスワードが空でも許可します。
        $password = isset($_POST["password"]) ? (string)$_POST["password"] : "";

        if (empty($_POST["edit_id"])) {
            // 編集モードでない場合、新しいエントリをデータベースに挿入します
            $post_time = date('Y-m-d H:i:s');
            $sql_insert = "INSERT INTO tbast (name, comment, post_time, password) VALUES (:name, :comment, :post_time, :password)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt_insert->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt_insert->bindParam(':post_time', $post_time, PDO::PARAM_STR);
            $stmt_insert->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt_insert->execute();
        } else {
            $edit_id = $_POST["edit_id"];
            $edit_password = (isset($_POST["edit_password"]) ? (string)$_POST["edit_password"] : "");

            if ($password ==="") {
                echo "";
            } else {
                // 編集時のパスワード確認
                $sql_check_password = "SELECT password FROM tbast WHERE id = :edit_id";
                $stmt_check_password = $pdo->prepare($sql_check_password);
                $stmt_check_password->bindParam(':edit_id', $edit_id, PDO::PARAM_INT);
                $stmt_check_password->execute();
                $result = $stmt_check_password->fetch();

                if (!empty($result) && $result['password'] === $password) {
                    // パスワードが正しい場合、名前とコメントを更新
                    $sql_update = "UPDATE tbast SET name = :name, comment = :comment WHERE id = :edit_id";
                    $stmt_update = $pdo->prepare($sql_update);
                    $stmt_update->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt_update->bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt_update->bindParam(':edit_id', $edit_id, PDO::PARAM_INT);
                    $stmt_update->execute();
                } else {
                    echo "";
                }
            }
        }
    }
}

if (!empty($_POST["edit"])) {
    $edit_id_variable = $_POST["edit_id"];
    $edit_password_variable = isset($_POST["edit_password"]) ? (string)$_POST["edit_password"] : "";

    // 編集時のパスワード確認
    if ($edit_password_variable ==="") {
        echo "";
    } else {
        $sql_edit = "SELECT * FROM tbast WHERE id = :edit_id AND password = :edit_password"; 
        $stmt_edit = $pdo->prepare($sql_edit);
        $stmt_edit->bindParam(':edit_id', $edit_id_variable, PDO::PARAM_INT);
        $stmt_edit->bindParam(':edit_password', $edit_password_variable, PDO::PARAM_STR); 
        $stmt_edit->execute();
        $result = $stmt_edit->fetchAll();
        foreach ($result as $entry) {
            if (!empty($entry['name']) && $entry['comment']) {
                $name_variable = $entry['name'];
                $comment_variable = $entry['comment'];
            }
        }    
    }
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>掲示板</title>
</head>
<body>
    <h1>掲示板</h1>
    <form action="" method="post">
        <input type="text" name="name" placeholder="名前" value="<?php echo $name_variable; ?>">
        <input type="text" name="comment" placeholder="コメント" value="<?php echo $comment_variable; ?>">
        <input type="password" name="password" placeholder="パスワード">
        <input type="hidden" name="edit_id" value="<?php echo $edit_id_variable; ?>">
        <input type="submit" name="submit" value="送信">
    </form>

    <form action="" method="post">
        <input type="text" name="delete_number" placeholder="削除対象番号">
        <input type="password" name="delete_password" placeholder="パスワード">
        <input type="submit" name="delete" value="削除">
    </form>
    <form action="" method="post">
        <input type="text" name="edit_id" placeholder="編集対象番号">
        <input type="password" name="edit_password" placeholder="パスワード">
        <input type="submit" name="edit" value="編集">
    </form>

    <?php
    $sql_select = "SELECT * FROM tbast ORDER BY id ASC"; 
    $stmt_select = $pdo->query($sql_select);

    $entries = $stmt_select->fetchAll();

    if (!empty($entries)) {
        foreach ($entries as $entry) {
            echo $entry['id'] . ",";
            echo htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8') . ",";
            echo htmlspecialchars($entry['comment'], ENT_QUOTES, 'UTF-8') . ",";
            echo $entry['post_time'] . "<br>";
        }
    } else {
        echo "";
    }
    ?>
</body>
</html>