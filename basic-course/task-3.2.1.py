"""
Напишите функцию update_dictionary(d, key, value), которая принимает на вход словарь d и два числа: key и value.

Если ключ key есть в словаре d, то добавьте значение value в список, который хранится по этому ключу.
Если ключа key нет в словаре, то нужно добавить значение в список по ключу 2⋅key. Если и ключа 2⋅key нет,
то нужно добавить ключ 2⋅key в словарь и сопоставить ему список из переданного элемента [value].

Требуется реализовать только эту функцию, кода вне неё не должно быть.
Функция не должна вызывать внутри себя функции input и print.
"""


def update_dictionary(d, key, value):
    if not d.get(key):
        if d.get(key * 2):
            d[key * 2] += [value]
        else:
            d[key * 2] = [value]
    else:
        d[key] += [value]


d = {}
print(update_dictionary(d, 1, -1))  # None
print(d)                            # {2: [-1]}
update_dictionary(d, 2, -2)
print(d)                            # {2: [-1, -2]}
update_dictionary(d, 1, -3)
print(d)                            # {2: [-1, -2, -3]}
