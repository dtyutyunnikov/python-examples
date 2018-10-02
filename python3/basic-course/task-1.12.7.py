num = input()

left = sum(map(int, num[0:3]))
right = sum(map(int, num[3:]))
print('Счастливый' if left == right else 'Обычный')
