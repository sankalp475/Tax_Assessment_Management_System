<?php
require_once '../config/database.php';
require_once '../models/Client.php';

class ClientController {
    private $db;
    private $client;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->client = new Client($this->db);
    }

    public function index() {
        $stmt = $this->client->read();
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once '../views/clients/index.php';
    }

    public function create() {
        require_once '../views/clients/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->client->pan = $_POST['pan'];
            $this->client->name = $_POST['name'];
            $this->client->fathers_name = $_POST['fathers_name'];
            $this->client->dob = $_POST['dob'];
            $this->client->category = $_POST['category'];
            $this->client->address = $_POST['address'];
            $this->client->phone = $_POST['phone'];
            $this->client->email = $_POST['email'];

            if (!$this->client->isPanUnique()) {
                $_SESSION['error'] = "PAN number already exists!";
                header("Location: /clients/create");
                exit();
            }

            if ($this->client->create()) {
                $_SESSION['success'] = "Client created successfully!";
                header("Location: /clients");
                exit();
            } else {
                $_SESSION['error'] = "Unable to create client.";
                header("Location: /clients/create");
                exit();
            }
        }
    }

    public function edit($pan) {
        $this->client->pan = $pan;
        if ($this->client->readOne()) {
            $client = [
                'pan' => $this->client->pan,
                'name' => $this->client->name,
                'fathers_name' => $this->client->fathers_name,
                'dob' => $this->client->dob,
                'category' => $this->client->category,
                'address' => $this->client->address,
                'phone' => $this->client->phone,
                'email' => $this->client->email
            ];
            require_once '../views/clients/edit.php';
        } else {
            $_SESSION['error'] = "Client not found!";
            header("Location: /clients");
            exit();
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->client->pan = $_POST['pan'];
            $this->client->name = $_POST['name'];
            $this->client->fathers_name = $_POST['fathers_name'];
            $this->client->dob = $_POST['dob'];
            $this->client->category = $_POST['category'];
            $this->client->address = $_POST['address'];
            $this->client->phone = $_POST['phone'];
            $this->client->email = $_POST['email'];

            if ($this->client->update()) {
                $_SESSION['success'] = "Client updated successfully!";
                header("Location: /clients");
                exit();
            } else {
                $_SESSION['error'] = "Unable to update client.";
                header("Location: /clients/edit/" . $this->client->pan);
                exit();
            }
        }
    }

    public function delete($pan) {
        $this->client->pan = $pan;
        if ($this->client->delete()) {
            $_SESSION['success'] = "Client deleted successfully!";
        } else {
            $_SESSION['error'] = "Unable to delete client.";
        }
        header("Location: /clients");
        exit();
    }

    public function view($pan) {
        $this->client->pan = $pan;
        if ($this->client->readOne()) {
            $client = [
                'pan' => $this->client->pan,
                'name' => $this->client->name,
                'fathers_name' => $this->client->fathers_name,
                'dob' => $this->client->dob,
                'category' => $this->client->category,
                'address' => $this->client->address,
                'phone' => $this->client->phone,
                'email' => $this->client->email,
                'created_at' => $this->client->created_at
            ];
            require_once '../views/clients/view.php';
        } else {
            $_SESSION['error'] = "Client not found!";
            header("Location: /clients");
            exit();
        }
    }
}
?>
