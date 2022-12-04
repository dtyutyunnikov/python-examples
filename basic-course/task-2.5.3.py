"""
Напишите программу, которая принимает на вход список чисел в одной строке и выводит на экран в одну строку значения,
которые повторяются в нём более одного раза.

Для решения задачи может пригодиться метод sort списка.

Выводимые числа не должны повторяться, порядок их вывода может быть произвольным.

Sample Input 1:
4 8 0 3 4 2 0 3

Sample Output 1:
0 3 4
"""

items = [int(x) for x in input().split()]
result = []
for item in items:
    if (items.count(item) > 1) and item not in result:
        result.append(item)
else:
    print(*result, end=' ')
