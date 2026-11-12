SELECT 'duplikaty_stanowisk' AS Regula,COUNT(*) AS Niepoprawne FROM (SELECT Nazwa_stanowiska FROM Stanowiska GROUP BY Nazwa_stanowiska HAVING COUNT(*)>1) AS duplikaty;
SELECT 'duplikaty_typow_pokoi' AS Regula,COUNT(*) AS Niepoprawne FROM (SELECT NazwaTypu FROM TypyPokoi GROUP BY NazwaTypu HAVING COUNT(*)>1) AS duplikaty;
