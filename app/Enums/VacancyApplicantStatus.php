<?php

namespace App\Enums;

enum VacancyApplicantStatus: string
{
    case REGISTERED = 'registered';
    case IN_INTERVIEW = 'in_interview';
    case EVALUATED = 'evaluated';
    case APT = 'apt';
    case NO_APT = 'no_apt';
}
