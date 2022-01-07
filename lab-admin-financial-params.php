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
    public const EXPENSE_TYPE_ADMINISTRATIVE = 3; 

    public function get_financial_type($typeid) {
        switch($typeid) {
            case FinancialParams::FINANCIAL_TYPE_CONTRACT: 
                return 'contract';
                break;
            case FinancialParams::FINANCIAL_TYPE_HISTORY: 
                return 'history';
                break;
            case FinancialParams::FINANCIAL_TYPE_SEMINAR:
                return 'seminar';
        }
    }
    public function save_financial($financial_type, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        global $wpdb;
        $wpdb->insert($wpdb->prefix.'lab_financial', array("financial_type"=>$financial_type, "object_id"=>$object_id, "eotp"=>$eotp, "funder_type"=>$funder_type, "expense_type"=>$expense_type, "expense_details"=>$expense_details, "amount"=>$amount ));
    }

    public function save_financial_contract($object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        return $this->save_financial(FinancialParams::FINANCIAL_TYPE_CONTRACT, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount);
    }

    public function save_financial_history($object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        return $this->save_financial(FinancialParams::FINANCIAL_TYPE_HISTORY, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount);
    }

    public function save_financial_seminar($object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        return $this->save_financial(FinancialParams::FINANCIAL_TYPE_SEMINAR, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount);
    }

    public function modify_financial($financial_type, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        global $wpdb;
        $wpdb->update($wpdb->prefix.'lab_financial', array("financial_type"=>$financial_type, "object_id"=>$object_id, "eotp"=>$eotp, "funder_type"=>$funder_type, "expense_type"=>$expense_type, "expense_details"=>$expense_details, "amount"=>$amount ));
    }

    public function modify_financial_contract($object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        return $this->modify_financial(FinancialParams::FINANCIAL_TYPE_CONTRACT, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount);
    }

    public function modify_financial_history($object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        return $this->modify_financial(FinancialParams::FINANCIAL_TYPE_HISTORY, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount);
    }

    public function modify_financial_seminar($object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount) {
        return $this->modify_financial(FinancialParams::FINANCIAL_TYPE_SEMINAR, $object_id, $eotp, $funder_type, $expense_type, $expense_details, $amount);
    }


    

  

}
 

// TODO: financial edit on contract edit ; financial delete on contract delete ; function to popuplate financial table




?>