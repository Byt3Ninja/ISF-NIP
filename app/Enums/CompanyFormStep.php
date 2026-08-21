<?php

namespace App\Enums;

enum CompanyFormStep: int
{
    case BasicInfo = 1;
    case ContactInfo = 2;
    case AboutDescription = 3;
    case FinancialInfo = 4;
    case TeamInfo = 5;
    case PitchDeckInfo = 6;
    case SocialWebsite = 8;
    case PitchDeckFile = 9;
    case PitchDeckVideo = 10;
    case SupplementaryDocuments = 11;
    case AboutProblemLegacy = 12;
    case BusinessModel = 13;
    case Classification = 14;
    case FinancialProjection = 15;
    case ExpertiseTags = 16;
}
