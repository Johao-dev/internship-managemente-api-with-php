<?php

namespace App\users;

use App\core\StoredProcedureExecutor;

class UserRepository {

    private StoredProcedureExecutor $executor;

    public function __construct() {
        $this->executor = StoredProcedureExecutor::getInstance();
    }

    public function create(UserEntity $user): bool {
        return $this->executor->execute(
            "CALL sp_create_user(:full_name, :institutional_email, :role, :password)",
            [
                'full_name'           => $user->full_name,
                'institutional_email' => $user->institutional_email,
                'role'                => $user->role,
                'password'            => $user->password
            ],
            false,
            null,
            true
        );
    }

    public function findById(int $id) {
        return $this->executor->execute(
            "CALL sp_get_by_id_user(:id)",
            ['id' => $id],
            false,
            UserEntity::class
        );
    }

    public function findByEmail(string $email) {
        return $this->executor->execute(
            "CALL sp_get_by_email_user(:email)",
            ['email' => $email],
            false,
            UserEntity::class
        );
    }

    public function findAll(): array {
        return $this->executor->execute(
            "CALL sp_get_all_users()",
            [],
            true,
            UserEntity::class
        ) ?? [];
    }

    public function findByRole(UserRole $role): array {
        return $this->executor->execute(
            "CALL sp_get_by_role_users(:role)",
            ['role' => $role->value],
            true,
            UserEntity::class
        ) ?? [];
    }

    public function update(UserEntity $user): bool {
        return $this->executor->execute(
            "CALL sp_update_user(:id, :full_name, :institutional_email, :role, :password, :active)",
            [
                'id'                  => $user->id,
                'full_name'           => $user->full_name,
                'institutional_email' => $user->institutional_email,
                'role'                => $user->role,
                'password'            => $user->password,
                'active'              => $user->active
            ],
            false,
            null,
            true
        );
    }

    public function delete(int $id): bool {
        return $this->executor->execute(
            "CALL sp_delete_user(:id)",
            ['id' => $id],
            false,
            null,
            true
        );
    }
}