<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_account',
        'department',
        'admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public static function getUserNickName($user): string
    {
        return explode("@", $user->email)[0];
    }

    public static function isAdmin($user): bool
    {
        return $user->admin == 1;
    }

    public static function isValidateEmail($user): bool
    {
        $validationEmailSuffix = 'example.com.tw';

        $emailSuffix = explode("@", $user->email)[1]; // gmail.com // yahoo.com.tw

        return $emailSuffix == $validationEmailSuffix;
    }

    public static function getDepartmentOptions(array $department): array
    {
        $data = [];

        foreach( $department as $value )
        {
            switch($value)
            {
                case 'guest':
                    $data['guest'] = '訪客';
                    break;
                case 'td':
                    $data['td'] = '工程部門';
                    break;
                case 'ad':
                    $data['ad'] = '管理部門';
                    break;
                case 'qa':
                    $data['qa'] = '品管部門';
                    break;
                case 'csd':
                    $data['csd'] = '營運行銷部';
                    break;
                case 'art':
                    $data['art'] = '美術部門';
                    break;
                case 'pd':
                    $data['pd'] = '企劃部門';
                    break;
                case 'pd&qa':
                    $data['pd&qa'] = '企劃部&品管部';
                    break;
            }
        }
        return $data;
    }

    public static function getDepartmentName(string $department): string
    {
        $departmentName = '';

        switch($department)
        {
            case 'guest':
                $departmentName = '訪客';
                break;
            case 'td':
                $departmentName = '工程部門';
                break;
            case 'ad':
                $departmentName = '管理部門';
                break;
            case 'qa':
                $departmentName = '品管部門';
                break;
            case 'csd':
                $departmentName = '營運行銷部';
                break;
            case 'art':
                $departmentName = '美術部門';
                break;
            case 'pd':
                $departmentName = '企劃部門';
                break;
            case 'pd&qa':
                $departmentName = '企劃部&品管部';
                break;
        }

        return $departmentName;
    }
    public static function getDepartmentStrToNumValue(string $department_str) // allreport // livewire blade參數無法傳string 之後定義全部更改為數字
    {
        switch($department_str)
        {
            case 'guest':
                return 0;
            case 'td':
                return 1;
            case 'ad':
                return 2;
            case 'qa':
                return 3;
            case 'csd':
                return 4;
            case 'art':
                return 5;
            case 'pd':
                return 6;
            case 'pd&qa':
                return 7;
        }
    }

    public static function getDepartmentNumToStrValue(int $department_num) // allreport // livewire blade參數無法傳string 之後定義全部更改為數字
    {
        switch($department_num)
        {
            case 0:
                return 'guest';
            case 1:
                return 'td';
            case 2:
                return 'ad';
            case 3:
                return 'qa';
            case 4:
                return 'csd';
            case 5:
                return 'art';
            case 6:
                return 'pd';
            case 7:
                return 'pd&qa';
        }
    }
}
