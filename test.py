import tkinter as tk
import random
import time
import math
class Game():
    def __init__(self):
        self.root = tk.Tk()
        self.root.bind("<Key>", self.key_pressed)
        self.root.bind("<Button-1>", self.mouse_clicked)
        self.canvas = tk.Canvas(self.root, width = 500, height = 600, bg = "#161823")
        self.canvas.config(highlightthickness=0)

        self.enemies = []
        self.create_player()
        for _ in range(5):
            self.create_enemy()

        self.root.protocol("WM_DELETE_WINDOW", self.on_closing)
        self.canvas.pack() 

        self.enemy_random_move()
        self.root.mainloop()
        #self.move()
        
    
    def move(self):
        while True:
            self.canvas.delete(self.player)
            x = random.randint(0,900)
            y = random.randint(0,300)
            self.player = self.canvas.create_rectangle(x, y, x + 20, y + 20, fill="#0000C6")
            self.canvas.update()
            time.sleep(0.01)

    def enemy_random_move(self):
        for enemy in self.enemies:
            self.canvas.move(enemy, 0, 10)
            if self.canvas.coords(enemy)[1] > 600:
                self.canvas.delete(enemy)
                self.enemies.remove(enemy)
                self.create_enemy()
        self.root.after(50, self.enemy_random_move)
    
    def create_player(self):
        x = random.randint(0,400)
        y = random.randint(0,400)
        self.player = self.canvas.create_rectangle(x, y, x + 20, y + 20, fill="#0000C6")

    def create_enemy(self):
        x = random.randint(0,400)
        y = random.randint(0,400)
        self.enemies.append(self.canvas.create_rectangle(x, y, x + 20, y + 20, fill="#EA8899"))

    def mouse_clicked(self, event):
        print(event)

    def key_pressed(self, event):
        x=0
        y=0
        if event.keysym == "Right":
            x=10
        elif event.keysym == "Left":
            x=-10
        elif event.keysym == "Up":
            y=-10
        elif event.keysym == "Down":
            y=10
        self.canvas.move(self.player, x, y)
        print(self.check_collision())

    def check_collision(self):
        player_coords = self.canvas.coords(self.player)
        enemy_coords = self.canvas.coords(self.enemy)
        if player_coords[0] < enemy_coords[0] + 20 and \
           player_coords[0] + 20 > enemy_coords[0] and \
           player_coords[1] < enemy_coords[1] + 20 and \
           player_coords[1] + 20 > enemy_coords[1]:
            return True
        return False

    def on_closing(self):
        self.root.destroy()

if __name__ == "__main__":
    Game() 