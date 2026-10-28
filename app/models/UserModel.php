
<?php

class UserModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create a new user account
    public function createUser($username, $email, $password, $fullName)
    {
        $sql = "INSERT INTO users
                (username, email, password, full_name)
                VALUES
                (:username, :email, :password, :full_name)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':full_name' => $fullName
        ]);
    }

    // Find a user by email for login
    public function findByEmail($email)
    {
        $sql = "SELECT id, username, email, password, full_name, bio,
                       profile_image
                FROM users
                WHERE email = :email
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);

        return $stmt->fetch();
    }

    // Find a user by ID
    public function findById($id)
    {
        $sql = "SELECT id, username, email, full_name, bio,
                       profile_image, created_at
                FROM users
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }

    // Search users by username or display name
    public function searchUsers(string $keyword): array
    {
        $sql = "SELECT id, username, full_name, bio,
                       profile_image, created_at
                FROM users
                WHERE username LIKE :username
                   OR full_name LIKE :full_name
                ORDER BY username ASC
                LIMIT 100";

        $searchTerm = '%' . $keyword . '%';

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ':username' => $searchTerm,
            ':full_name' => $searchTerm
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check whether a username already exists
    public function usernameExists($username)
    {
        $sql = "SELECT id
                FROM users
                WHERE username = :username
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':username' => $username]);

        return $stmt->fetch() !== false;
    }

    // Check whether an email already exists
    public function emailExists($email)
    {
        $sql = "SELECT id
                FROM users
                WHERE email = :email
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);

        return $stmt->fetch() !== false;
    }

    // Update a user's display name and bio
    // A null profile image keeps the existing image unchanged
    public function updateProfile($id, $fullName, $bio, $profileImage = null)
    {
        $sql = "UPDATE users
                SET full_name = :full_name,
                    bio = :bio,
                    profile_image = COALESCE(:profile_image, profile_image)
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':full_name' => $fullName,
            ':bio' => $bio,
            ':profile_image' => $profileImage,
            ':id' => $id
        ]);
    }
}