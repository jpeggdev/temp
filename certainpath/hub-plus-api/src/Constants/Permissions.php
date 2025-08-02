<?php

declare(strict_types=1);

namespace App\Constants;

class Permissions
{
    // Universal Access Permissions
    public const string CAN_SWITCH_COMPANY_ALL = 'CAN_SWITCH_COMPANY_ALL';
    public const string CAN_SWITCH_COMPANY_MARKETING_ONLY = 'CAN_SWITCH_COMPANY_MARKETING_ONLY';

    // User Management Permissions
    public const string CAN_MANAGE_USERS = 'CAN_MANAGE_USERS';
    public const string CAN_VIEW_USERS = 'CAN_VIEW_USERS';
    public const string CAN_CREATE_USERS = 'CAN_CREATE_USERS';

    // Document Management Permissions
    public const string CAN_ACCESS_DOCUMENT_LIBRARY = 'CAN_ACCESS_DOCUMENT_LIBRARY';
    public const string CAN_ACCESS_MONTHLY_BALANCE_SHEET = 'CAN_ACCESS_MONTHLY_BALANCE_SHEET';
    public const string CAN_ACCESS_PROFIT_AND_LOSS = 'CAN_ACCESS_PROFIT_AND_LOSS';
    public const string CAN_ACCESS_TRANSACTION_LIST = 'CAN_ACCESS_TRANSACTION_LIST';

    // Data Access Permissions
    public const string CAN_ACCESS_DATA_CONNECTOR = 'CAN_ACCESS_DATA_CONNECTOR';

    // Stochastic Management Permissions
    public const string CAN_MANAGE_PROSPECTS = 'CAN_MANAGE_PROSPECTS';
    public const string CAN_MANAGE_CUSTOMERS = 'CAN_MANAGE_CUSTOMERS';

    // Event Registration Permissions
    public const string CAN_MANAGE_EVENT_REGISTRATION = 'CAN_MANAGE_EVENT_REGISTRATION';
}
