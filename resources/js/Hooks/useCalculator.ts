import { useState, useMemo } from 'react';

interface CalculatorInputs {
    price: number;
    downPaymentPercent: number;
    interestRate: number;
    closingCosts: number;
    rehab: number;
    monthlyRent: number;
    loanTermYears: number;
    taxes: number;
    insurance: number;
    utilities: number;
    maintenance: number;
    miscellaneous: number;
    capExPercent: number;
    propertyManagementPercent: number;
    vacancyPercent: number;
}

interface Calculations {
    loanAmount: number;
    monthlyMortgage: number;
    totalMonthlyExpenses: number;
    monthlyCashflow: number;
    annualCashflow: number;
    noi: number;
    capRate: number;
    cashInvested: number;
    cashOnCash: number;
    annualPrincipalPaydown: number;
    totalROI: number;
}

export function useCalculator() {
    const [inputs, setInputs] = useState<CalculatorInputs>({
        price: 350000,
        downPaymentPercent: 20,
        interestRate: 9.0,
        closingCosts: 0,
        rehab: 0,
        monthlyRent: 2400,
        loanTermYears: 30,
        taxes: 200,
        insurance: 100,
        utilities: 50,
        maintenance: 100,
        miscellaneous: 50,
        capExPercent: 8,
        propertyManagementPercent: 10,
        vacancyPercent: 5,
    });

    const updateInput = (key: keyof CalculatorInputs, value: number) => {
        setInputs((prev) => ({ ...prev, [key]: value }));
    };

    const getExpenseDollarAmount = (percent: number): number => {
        return (inputs.monthlyRent * percent) / 100;
    };

    const calculations = useMemo((): Calculations => {
        // Loan Amount
        const downPaymentAmount = (inputs.price * inputs.downPaymentPercent) / 100;
        const loanAmount = inputs.price - downPaymentAmount;

        // Monthly Mortgage Payment using Amortization Formula
        // M = P [ r(1+r)^n ] / [ (1+r)^n – 1 ]
        const monthlyInterestRate = inputs.interestRate / 100 / 12;
        const numberOfPayments = inputs.loanTermYears * 12;
        
        let monthlyMortgage = 0;
        if (monthlyInterestRate > 0) {
            const numerator = loanAmount * monthlyInterestRate * Math.pow(1 + monthlyInterestRate, numberOfPayments);
            const denominator = Math.pow(1 + monthlyInterestRate, numberOfPayments) - 1;
            monthlyMortgage = numerator / denominator;
        } else {
            monthlyMortgage = loanAmount / numberOfPayments;
        }

        // Monthly Expenses
        const capExDollar = getExpenseDollarAmount(inputs.capExPercent);
        const propertyMgmtDollar = getExpenseDollarAmount(inputs.propertyManagementPercent);
        const vacancyDollar = getExpenseDollarAmount(inputs.vacancyPercent);

        const totalMonthlyExpenses =
            monthlyMortgage +
            inputs.taxes +
            inputs.insurance +
            inputs.utilities +
            inputs.maintenance +
            inputs.miscellaneous +
            capExDollar +
            propertyMgmtDollar +
            vacancyDollar;

        // Cashflow
        const monthlyCashflow = inputs.monthlyRent - totalMonthlyExpenses;
        const annualCashflow = monthlyCashflow * 12;

        // NOI (Net Operating Income) - excludes mortgage
        const annualOperatingExpenses =
            (inputs.taxes +
            inputs.insurance +
            inputs.utilities +
            inputs.maintenance +
            inputs.miscellaneous +
            capExDollar +
            propertyMgmtDollar +
            vacancyDollar) * 12;
        
        const noi = (inputs.monthlyRent * 12) - annualOperatingExpenses;

        // Cap Rate
        const capRate = inputs.price > 0 ? (noi / inputs.price) * 100 : 0;

        // Cash on Cash
        const cashInvested = downPaymentAmount + inputs.closingCosts + inputs.rehab;
        const cashOnCash = cashInvested > 0 ? (annualCashflow / cashInvested) * 100 : 0;

        // Annual Principal Paydown (First Year Approximation)
        const annualMortgagePayment = monthlyMortgage * 12;
        const annualInterest = loanAmount * (inputs.interestRate / 100);
        const annualPrincipalPaydown = annualMortgagePayment - annualInterest;

        // Total ROI
        const totalROI = cashInvested > 0 
            ? ((annualCashflow + annualPrincipalPaydown) / cashInvested) * 100 
            : 0;

        return {
            loanAmount,
            monthlyMortgage,
            totalMonthlyExpenses,
            monthlyCashflow,
            annualCashflow,
            noi,
            capRate,
            cashInvested,
            cashOnCash,
            annualPrincipalPaydown,
            totalROI,
        };
    }, [inputs]);

    return {
        inputs,
        updateInput,
        calculations,
        getExpenseDollarAmount,
    };
}
