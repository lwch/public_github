<?php
class TaskUserFollowers extends Task {
    protected $id;
    protected $name;
    protected $followers_url;

    public function __construct($id, $name, $followers_url) {
        $this->id            = $id;
        $this->name          = $name;
        $this->followers_url = $followers_url;
        parent::__construct();
    }

    public function type() {
        return 'TaskUserFollowers';
    }

    public function run() {
        list($status, $header, $body) = curl_get($this->append_token($this->followers_url));
        if ($status != 200) {
            if ($status == 403) $this->err_no = static::$err_403;
            else $this->err_no = static::$err_crashed;
            return false;
        }
        $body = json_decode($body, true);
        $pdo = db();
        $ref = $this->id;
        $sql = "DELETE FROM `followers` WHERE `ref` = '$ref'";
        if ($pdo->exec($sql) === false) {
            $this->err_no = static::$err_crashed;
            return false;
        }
        foreach ($body as $row) {
            $id = $row['id'];
            $sql = "INSERT INTO `followers`(`id`, `ref`) VALUES('$id', '$ref')";
            if ($pdo->exec($sql) === false) {
                $this->err_no = static::$err_crashed;
                return false;
            }
            #context()->push_task(new TaskUserDetail($row['id'], $row['login'], $row['url'], $row['followers_url'], $row['repos_url']));
        }
        return true;
    }
}
