"""
На прошлой неделе мы сжимали строки, используя кодирование повторов.
Теперь нашей задачей будет восстановление исходной строки обратно.

Напишите программу, которая считывает из файла строку, соответствующую тексту, сжатому с помощью кодирования повторов,
и производит обратную операцию, получая исходный текст.

Запишите полученный текст в файл и прикрепите его, как ответ на это задание.

В исходном тексте не встречаются цифры, так что код однозначно интерпретируем.

Sample Input:
a3b4c2e10b1

Sample Output:
aaabbbbcceeeeeeeeeeb
"""

new_line = ''

with open('in.txt') as f1:
    line = f1.readline().strip()
    current = ''
    multiplier = '0'
    for s in line:
        if s.isalpha():
            new_line += current * int(multiplier)
            multiplier = '0'
            current = s
            continue
        else:
            multiplier += s
    else:
        new_line += current * int(multiplier)

with open('out.txt', 'w') as f2:
    f2.write(new_line)
