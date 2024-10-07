import tkinter as tk
import random
root = tk.Tk()
canvas = tk.Canvas(root,width=400,height=600,bg="#80FFFF")
canvas.pack()
player = canvas.create_oval(120, 120, 150, 150, fill="white")
enemies=[]
def create_enemy():
    x = random.randint(0,400)
    y = random.randint(0,400)
    enemy = canvas.create_rectangle(x,y,x+20,y+20,fill="blue")
    enemies.append(enemy)
def move_player(event):
    x, y = 0 ,0
    key = event.keysym
    if key == "Up":
        y = -10
    elif key == "Down":
        y = 10
    elif key == "Left":
        x = -10
    elif key == "Right":
        x = 10
    canvas.move(player, x, y)
root.bind("<Key>", move_player)
for _ in range(10):
    create_enemy()
def main_loop():
    for enemy in enemies:
        canvas.move(enemy,0,5)
        if canvas.coords(enemy)[1]>500:
            canvas.delete(enemy)
            enemies.remove(enemy)
            create_enemy()
    root.after(50,main_loop)
main_loop()
root.mainloop()
