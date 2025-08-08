<?php

namespace App\Http\Controllers\API;

use App\Models\Intervenant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Client;
use Illuminate\Support\Facades\Password;
use App\Mail\ClientConfirmationMail;
use App\Mail\IntervenantConfirmationMail;
use App\Mail\PasswordResetMail;
use App\Models\IntervenantDisponibilite;
use App\Models\IntervenantDocument;
use App\Repositories\Interfaces\IntervenantRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;


class IntervenantController extends Controller
{


    protected $intervenantRepository;

    public function __construct(IntervenantRepositoryInterface $intervenantRepository)
    {
        $this->intervenantRepository = $intervenantRepository;
    }


    public function store(Request $request)
    {
        return  $this->intervenantRepository->store($request);
    }
}
