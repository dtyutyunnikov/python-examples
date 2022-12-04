"""
Напишите программу, на вход которой подается одна строка с целыми числами. Программа должна вывести сумму этих чисел.

Используйте метод split строки. ﻿﻿

Sample Input:
4 -1 9 3

Sample Output:
15
"""

res = 0
ls = [int(i) for i in input().split()]
for l in ls:
    res += l
print(res)
