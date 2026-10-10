<?php
namespace App\Domain\Repositories;
use App\Domain\Entities\Loan;

interface LoanRepository {
    public function save(Loan $loan): void;
    public function findById(string $id): ?Loan;
}