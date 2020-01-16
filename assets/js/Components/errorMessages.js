const ERROR_NOT_ENOUGH_MONEY = 505;
const ERROR_SKILL_NOT_FOUND = 506;
const ERROR_MAX_LEVEL = 507;

export function getErrorMessageByCode(code) {
    switch (code) {
        case ERROR_NOT_ENOUGH_MONEY: {
            return 'You don\'t have enough money.';
        }
        case ERROR_MAX_LEVEL: {
            return 'You reached max level!';
        }
        case ERROR_SKILL_NOT_FOUND: {
            return 'Something went wrong.';
        }
        default: {
            return 'Unknown error occurred.';
        }
    }
}