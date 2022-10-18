<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class TestAuthFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $route = Route::currentRouteName();

        switch ($route) {
            case 'test.client.register':
                return [
                    'name' => 'required|max:50',
                    'email' => 'required|email|unique:users,email',
                    'image' => 'nullable',
                    'image.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    'phone' => "phone:ID",
                    'password' => 'required|same:password_confirmation',
                ];

                break;

            case 'test.client.verify':
                return [
                    'token' => 'required',
                ];

                break;

            case 'test.client.resend':
                return [
                    'email' => 'required|email',
                ];

                case 'test.client.resend.reset':
                    return [
                        'email' => 'required|email',
                    ];

                    case 'test.client.check.reset':
                        return [
                            'token' => 'required',
                        ];
                        break;

                        case 'test.client.reset':
                            return [
                                'old_password' => 'required',
                                'password' => 'required|same:password_confirmation',
                            ];
                            break;

                            case 'test.reset.form':
                                if (is_null($this->header('token'))) {
                                    throw new Exception("Token is null");
                                }
                                return [
                                ];
                                break;

                                case 'test.client.reset':
                                    # code...
                                    break;

                break;

            default:
                // code...
                break;
        }
    }
}
