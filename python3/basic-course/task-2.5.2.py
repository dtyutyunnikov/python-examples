"""
Напишите программу, на вход которой подаётся список чисел одной строкой.
Программа должна для каждого элемента этого списка вывести сумму двух его соседей.
Для элементов списка, являющихся крайними, одним из соседей считается элемент,
находящий на противоположном конце этого списка. Например, если на вход подаётся список "1 3 5 6 10",
то на выход ожидается список "13 6 9 15 7" (без кавычек).

Если на вход пришло только одно число, надо вывести его же.

Вывод должен содержать одну строку с числами нового списка, разделёнными пробелом.

Sample Input 1:
1 3 5 6 10

Sample Output 1:
13 6 9 15 7
"""

import sys

items = [int(i) for i in input().split()]
result = []
if len(items) == 1:
    print(items[0])
    sys.exit()
for index, item in enumerate(items):
    if index == 0:
        result.append(items[1] + items[-1])
    elif index == len(items) - 1:
        result.append(items[-2] + items[0])
    else:
        result.append(items[index - 1] + items[index + 1])
print(' '.join(str(s) for s in result))
