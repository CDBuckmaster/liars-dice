<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpFoundation\Response;

class CreateGameRequest extends FormRequest
{
  public function rules(): array
  {
    return [
      'players' => 'required|array|min:2|max:4',
      'players.*' => 'required|string',
    ];
  }

  public function failedValidation(Validator $validator)
  {
    throw new HttpResponseException(response()->json([
      'success'   => false,
      'message'   => 'Validation errors',
      'data'      => $validator->errors()
    ], Response::HTTP_UNPROCESSABLE_ENTITY));
  }
}
