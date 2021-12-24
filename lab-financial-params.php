<?php

class FinancialParams {

    /* Financial type */

    public const FINANCIAL_TYPE_CONTRACT = 1;
    public const FINANCIAL_TYPE_HISTORY = 2;
    public const FINANCIAL_TYPE_SEMINAR = 3;

    /* Funder type */

    public const FUNDER_TYPE_AMU = 1;
    public const FUNDER_TYPE_CNRS = 2;
    public const FUNDER_TYPE_MISC = 3;

    /* Expense type */
    
    public const EXPENSE_TYPE_STAFF = 1;
    public const EXPENSE_TYPE_INVESTMENT = 2;
    public const EXPENSE_TYPE_ADMINISTRATIVE = 3; /* TODO: choisir terme administrative/operating  */

    public function get_financial_type($typeid) {
        switch($typeid) {
            case FinancialParams::FINANCIAL_TYPE_CONTRACT: 
                return 'contract';
                break;
            case FinancialParams::FINANCIAL_TYPE_CONTRACT: 
                return 'contract';
                break;
            default:
            return 'contract';
        }
    }
    public function save_financial($financial_type, $object_id, $eotp, $funder_type, $expense_type, $expense_detail, $amount) {
        //
        return ;
    }

    public function save_financial_contract($object_id, $eotp, $funder_type, $expense_type, $expense_detail, $amount) {
        return $this->save_financial(FinancialParams::FINANCIAL_TYPE_CONTRACT, $object_id, $eotp, $funder_type, $expense_type, $expense_detail, $amount);
    }

    public function save_financial_history($object_id, $eotp, $funder_type, $expense_type, $expense_detail, $amount) {
        return $this->save_financial(FinancialParams::FINANCIAL_TYPE_HISTORY, $object_id, $eotp, $funder_type, $expense_type, $expense_detail, $amount);
    }

    public function save_financial_seminar($object_id, $eotp, $funder_type, $expense_type, $expense_detail, $amount) {
        return $this->save_financial(FinancialParams::FINANCIAL_TYPE_SEMINAR, $object_id, $eotp, $funder_type, $expense_type, $expense_detail, $amount);
    }

    

    
    //TODO:
    // modify

    // delete * 2 : delete by id, delete by object _id

    // list funder type
    // list expense type

}
 




?>