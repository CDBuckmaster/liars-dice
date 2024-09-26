<?php

namespace App\Http\Requests;

use App\Domain\Commands\PlayerActionCommandFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpFoundation\Response;

class CreatePlayerActionRequest extends FormRequest
{
  public function rules(): array
  {
    return [
      'action' => ['required', 'string', 'in:' . implode(',', PlayerActionCommandFactory::$actionTypes)],
      'player' => ['required', 'string'],
      'arguments' => ['array'],
      'arguments.quantity' => ['integer', 'required_if:action,make_bid'],
      'arguments.face' => ['integer', 'required_if:action,make_bid'],
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
