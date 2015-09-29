<?php
class TaskUserDetail extends Task {
    protected $id;
    protected $name;
    protected $url;
    protected $followers_url;
    protected $repos_url;

    public function __construct($id, $name, $url, $followers_url, $repos_url) {
        $this->id            = $id;
        $this->name          = $name;
        $this->url           = $url;
        $this->followers_url = $followers_url;
        $this->repos_url     = $repos_url;
        parent::__construct();
    }

    public function type() {
        return 'TaskUserDetail';
    }

    public function run() {
        list($status, $header, $body) = curl_get($this->append_token($this->url));
        if ($status != 200) {
            if ($status == 403) $this->err_no = static::$err_403;
            else $this->err_no = static::$err_crashed;
            return false;
        }
        $body = json_decode($body, true);
        $pdo = db();
        $sql = "SELECT COUNT(1) FROM `users` WHERE `id` = '${body['id']}' AND UNIX_TIMESTAMP(`updated`) = UNIX_TIMESTAMP('${body['updated_at']}')";
        list($cnt) = $pdo->query($sql)->fetch(PDO::FETCH_NUM);
        if ($cnt) {
            echo 'user: ', $this->name, ' exists and not updated ... skiped', "\n";
            return true;
        }
        $obj = array(
            'id'           => $body['id'],
            'created'      => $body['created_at'],
            'updated'      => $body['updated_at'],
            'name'         => $body['login'],
            'avatar_url'   => $body['avatar_url'],
            'company'      => $body['company'],
            'blog'         => $body['blog'],
            'location'     => $body['location'],
            'email'        => $body['email'],
            'public_repos' => $body['public_repos'],
            'public_gists' => $body['public_gists'],
            'followers'    => $body['followers'],
            'following'    => $body['following'],
            'admin'        => $body['site_admin'] ? 1 : 0,
        );
        if (insert_or_update($pdo, 'users', $obj) === false) {
            $this->err_no = static::$err_crashed;
            return false;
        }
        context()->push_task(new TaskUserFollowers($this->id, $this->name, $this->followers_url));
        return true;
    }
}
