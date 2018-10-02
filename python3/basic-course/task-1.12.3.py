x = float(input())
y = float(input())
mode = input()
if mode in ['mod', '/', 'div'] and y == 0:
    print('Деление на 0!')
elif mode == 'pow':
    print(pow(x, y))
elif mode == 'mod':
    print(x % y)
elif mode == 'div':
    print(x // y)
else:
    print(eval('{0!s} {1!s} {2!s}'.format(x, mode, y)))
